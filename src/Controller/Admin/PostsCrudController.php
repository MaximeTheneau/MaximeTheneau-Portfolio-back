<?php

namespace App\Controller\Admin;

use App\Entity\Posts;
use App\Entity\ParagraphPosts;
use App\Message\TriggerNextJsBuild;
use App\Service\ImageOptimizer;
use App\Service\MarkdownProcessor;
use App\Service\UrlGeneratorService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\HttpFoundation\File\File;
use DateTime as GlobalDateTime;
use IntlDateFormatter;
use DOMDocument;

class PostsCrudController extends AbstractCrudController
{
    private SluggerInterface $slugger;
    private ImageOptimizer $imageOptimizer;
    private MarkdownProcessor $markdownProcessor;
    private UrlGeneratorService $urlGeneratorService;
    private MessageBusInterface $messageBus;
    private EntityManagerInterface $entityManager;
    private string $projectDir;

    public function __construct(
        SluggerInterface $slugger,
        ImageOptimizer $imageOptimizer,
        MarkdownProcessor $markdownProcessor,
        UrlGeneratorService $urlGeneratorService,
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager,
        string $projectDir
    ) {
        $this->slugger = $slugger;
        $this->imageOptimizer = $imageOptimizer;
        $this->markdownProcessor = $markdownProcessor;
        $this->urlGeneratorService = $urlGeneratorService;
        $this->messageBus = $messageBus;
        $this->entityManager = $entityManager;
        $this->projectDir = $projectDir;
    }

    public static function getEntityFqcn(): string
    {
        return Posts::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Post')
            ->setEntityLabelInPlural('Posts')
            ->setSearchFields(['title', 'heading', 'slug', 'metaDescription'])
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            yield IdField::new('id')->hideOnForm();
            yield TextField::new('title', 'Titre');
            yield AssociationField::new('category', 'Catégorie');
            yield TextField::new('imgPost', 'Image')->setTemplatePath('admin/fields/image_url.html.twig');
            yield DateTimeField::new('createdAt', 'Date de création');
            yield DateTimeField::new('updatedAt', 'Mise à jour');
            return;
        }

        yield TextField::new('title', 'Titre')->setColumns(6);
        yield TextField::new('heading', 'En-tête')->setColumns(6);
        yield TextareaField::new('metaDescription', 'Meta Description')
            ->setHelp('Max 160 caractères')
            ->setColumns(12);

        yield TextField::new('slug', 'Slug')
            ->setHelp('Laissez vide pour générer automatiquement')
            ->setColumns(6);

        yield TextEditorField::new('contents', 'Contenu')
            ->setColumns(12);

        yield AssociationField::new('category', 'Catégorie')->setColumns(6);
        yield AssociationField::new('subcategory', 'Sous-catégorie')->setColumns(6);
        yield AssociationField::new('subtopic', 'Sous-thèmes')
            ->setColumns(6);

        // Image upload : le fichier est uploadé localement puis envoyé vers S3
        yield ImageField::new('imgPost', 'Image principale')
            ->setBasePath('')
            ->setUploadDir('public/uploads/tmp/')
            ->setUploadedFileNamePattern('[timestamp]-[slug].[extension]')
            ->setRequired(false)
            ->setColumns(6);

        yield TextField::new('altImg', 'Alt Image')
            ->setHelp('Laissez vide pour utiliser le titre')
            ->setColumns(6);

        yield UrlField::new('video', 'Lien Vidéo')->setColumns(6)->hideOnIndex();
        yield UrlField::new('github', 'Lien GitHub')->setColumns(6)->hideOnIndex();
        yield UrlField::new('website', 'Lien Website')->setColumns(6)->hideOnIndex();

        yield BooleanField::new('isHomeImage', 'Afficher sur la page d\'accueil')
            ->setColumns(6);

        yield CollectionField::new('paragraphPosts', 'Paragraphes')
            ->useEntryCrudForm(ParagraphPostsCrudController::class)
            ->setColumns(12);

        yield AssociationField::new('relatedPosts', 'Posts associés')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])
            ->setColumns(12);

        yield AssociationField::new('skills', 'Compétences')
            ->setColumns(12);

        if ($pageName === Crud::PAGE_DETAIL) {
            yield DateTimeField::new('createdAt', 'Date de création');
            yield DateTimeField::new('updatedAt', 'Date de mise à jour');
            yield TextField::new('url', 'URL générée');
            yield TextField::new('formattedDate', 'Date formatée');
            yield IntegerField::new('imgWidth', 'Largeur image');
            yield IntegerField::new('imgHeight', 'Hauteur image');
            yield TextField::new('srcset', 'Srcset');
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Posts) {
            return;
        }

        $this->processPost($entityInstance, true);
        $this->processImage($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Posts) {
            return;
        }

        $this->processPost($entityInstance, false);
        $this->processImage($entityInstance);

        $message = new TriggerNextJsBuild('Build');
        $this->messageBus->dispatch($message);

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function processImage(Posts $post): void
    {
        $imgPost = $post->getImgPost();

        // Si imgPost est une URL (existante sur S3), ne rien faire
        if (empty($imgPost) || str_starts_with($imgPost, 'http')) {
            // Pas de nouvelle image uploadée
            if (empty($imgPost)) {
                $post->setImgPost('Accueil');
                $post->setAltImg('Image de présentation');
            }
            return;
        }

        // Nouvelle image uploadée localement par EasyAdmin (filename relatif)
        $localPath = $this->projectDir . '/public/uploads/tmp/' . $imgPost;

        if (file_exists($localPath)) {
            $file = new File($localPath);
            $slug = $post->getSlug();
            $this->imageOptimizer->setPicture($file, $post, $slug);
            // ImageOptimizer::setPicture gère déjà :
            // - Upload vers S3
            // - Conversion WebP
            // - Mise à jour de imgPost avec l'URL S3
            // - Mise à jour srcset, imgWidth, imgHeight
            // - Suppression du fichier local (unlink)
        }
    }

    private function processPost(Posts $post, bool $isNew): void
    {
        // SLUG
        if (empty($post->getSlug()) || $post->getSlug() !== 'Accueil') {
            if (empty($post->getSlug())) {
                $slug = $this->createSlug($post->getTitle());
                $post->setSlug($slug);
            }
        }

        $slug = $post->getSlug();

        // URL
        if ($slug !== 'Accueil') {
            $categorySlug = $post->getCategory() ? $post->getCategory()->getSlug() : null;
            $subcategorySlug = $post->getSubcategory() ? $post->getSubcategory()->getSlug() : null;
            $url = $this->urlGeneratorService->generatePath($slug, $categorySlug, $subcategorySlug);
            $post->setUrl($url);
        } else {
            $post->setUrl('');
        }

        // ALT IMG
        if (empty($post->getAltImg())) {
            $post->setAltImg($post->getTitle());
        }

        // Lazy loading images dans le contenu HTML (TextEditorField produit du HTML)
        if (!empty($post->getContents())) {
            $htmlText = $post->getContents();

            $dom = new DOMDocument();
            @$dom->loadHTML(
                mb_convert_encoding($htmlText, 'HTML-ENTITIES', 'UTF-8'),
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );
            $images = $dom->getElementsByTagName('img');

            foreach ($images as $image) {
                $image->setAttribute('loading', 'lazy');
            }

            $htmlTextWithLazyLoading = $dom->saveHTML();
            $post->setContents($htmlTextWithLazyLoading);
        }

        // DATE
        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'dd MMMM yyyy');

        if ($isNew) {
            $post->setCreatedAt(new GlobalDateTime());
            $createdAt = $formatter->format($post->getCreatedAt());
            $post->setFormattedDate('Publié le ' . $createdAt);
        } else {
            $post->setUpdatedAt(new GlobalDateTime());
            $updatedDate = $formatter->format($post->getUpdatedAt());
            $createdAt = $formatter->format($post->getCreatedAt());
            $post->setFormattedDate('Publié le ' . $createdAt . '. Mise à jour le ' . $updatedDate);
        }

        // PARAGRAPHS
        foreach ($post->getParagraphPosts() as $paragraph) {
            $this->processParagraph($paragraph, $post);
        }
    }

    private function processParagraph(ParagraphPosts $paragraph, Posts $post): void
    {
        // Lazy loading images dans le paragraphe HTML
        if (!empty($paragraph->getParagraph())) {
            $htmlText = $paragraph->getParagraph();

            $dom = new DOMDocument('1.0', 'UTF-8');
            @$dom->loadHTML(
                mb_convert_encoding($htmlText, 'HTML-ENTITIES', 'UTF-8'),
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            $images = $dom->getElementsByTagName('img');
            foreach ($images as $image) {
                $image->setAttribute('loading', 'lazy');
            }

            $htmlTextWithLazyLoading = $dom->saveHTML();
            $paragraph->setParagraph($htmlTextWithLazyLoading);
        }

        // SLUG
        if (!empty($paragraph->getSubtitle())) {
            $slugPara = $this->createSlug($paragraph->getSubtitle());
            $slugPara = substr($slugPara, 0, 30);
            $paragraph->setSlug($slugPara);

            $categoryLink = $post->getCategory() ? $post->getCategory()->getSlug() : null;
            if ($categoryLink === 'Pages') {
                $paragraph->setLinkSubtitle('/' . $slugPara);
            } else {
                $paragraph->setLinkSubtitle('/' . $categoryLink . '/' . $slugPara);
            }
        } else {
            $this->entityManager->remove($paragraph);
        }

        // ALT IMG
        if (empty($paragraph->getAltImg()) && !empty($paragraph->getSubtitle())) {
            $paragraph->setAltImg($paragraph->getSubtitle());
        }
    }

    private function createSlug(string $inputString): string
    {
        return strtolower($this->slugger->slug($inputString)->slice(0, 50)->toString());
    }
}
