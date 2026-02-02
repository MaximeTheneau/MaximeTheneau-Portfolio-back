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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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

    public function __construct(
        SluggerInterface $slugger,
        ImageOptimizer $imageOptimizer,
        MarkdownProcessor $markdownProcessor,
        UrlGeneratorService $urlGeneratorService,
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager
    ) {
        $this->slugger = $slugger;
        $this->imageOptimizer = $imageOptimizer;
        $this->markdownProcessor = $markdownProcessor;
        $this->urlGeneratorService = $urlGeneratorService;
        $this->messageBus = $messageBus;
        $this->entityManager = $entityManager;
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

    // public function configureActions(Actions $actions): Actions
    // {
    //     return $actions
    //         ->add(Crud::PAGE_INDEX, Action::DETAIL);
    // }
 public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
        ];
    }
    // public function configureFields(string $pageName): iterable
    // {
    //     // Champs pour l'index (liste)
    //     // if ($pageName === Crud::PAGE_INDEX) {
    //     //     yield TextField::new('title', 'Titre');
    //     //     yield AssociationField::new('category', 'Catégorie');
    //     //     yield DateTimeField::new('createdAt', 'Date de création');
    //     //     yield DateTimeField::new('updatedAt', 'Mise à jour');
    //     //     return;
    //     // }

    //     // yield TextField::new('title', 'Titre')->setColumns(6);
    //     // yield TextField::new('heading', 'En-tête')->setColumns(6);
    //     // yield TextField::new('slug', 'Slug')
    //     //     ->setHelp('Laissez vide pour générer automatiquement')
    //     //     ->setColumns(6);

    //     yield TextareaField::new('metaDescription', 'Meta Description')
    //         ->setHelp('Max 160 caractères')
    //         ->setColumns(12);

    //     // yield TextareaField::new('contents', 'Contenu (Markdown)')
    //     //     ->setHelp('Utilisez le format Markdown, sera converti en HTML automatiquement')
    //     //     ->setColumns(12);

    //     // yield AssociationField::new('category', 'Catégorie')->setColumns(6);
    //     // yield AssociationField::new('subcategory', 'Sous-catégorie')
    //     //     ->setColumns(6);

    //     // yield TextField::new('imgPost', 'Image principale')
    //     //     ->setColumns(6);

    //     // yield TextField::new('altImg', 'Alt Image')
    //     //     ->setHelp('Laissez vide pour utiliser le titre')
    //     //     ->setColumns(6);

    //     // yield UrlField::new('github', 'Lien GitHub')->setColumns(6);
    //     // yield UrlField::new('website', 'Lien Website')->setColumns(6);
    //     // yield UrlField::new('video', 'Lien Vidéo')->setColumns(6);

    //     // yield BooleanField::new('isHomeImage', 'Afficher sur la page d\'accueil')
    //     //     ->setColumns(6);

    //     // yield IntegerField::new('imgWidth', 'Largeur image')->setColumns(4);
    //     // yield IntegerField::new('imgHeight', 'Hauteur image')->setColumns(4);
    //     // yield TextField::new('srcset', 'Srcset')->setColumns(12);

    //     // yield AssociationField::new('paragraphPosts', 'Paragraphes')
    //     //     ->setFormTypeOptions([
    //     //         'by_reference' => false,
    //     //     ])
    //     //     ->setColumns(12);

    //     // yield AssociationField::new('relatedPosts', 'Posts associés')
    //     //     ->setFormTypeOptions([
    //     //         'by_reference' => false,
    //     //     ]);

    //     // if ($pageName === Crud::PAGE_DETAIL) {
    //     //     yield DateTimeField::new('createdAt', 'Date de création');
    //     //     yield DateTimeField::new('updatedAt', 'Date de mise à jour');
    //     //     yield TextField::new('url', 'URL générée');
    //     //     yield TextField::new('formattedDate', 'Date formatée');
    //     // }
    // }

    // public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    // {
    //     if (!$entityInstance instanceof Posts) {
    //         return;
    //     }

    //     $this->processPost($entityInstance, true);
    //     parent::persistEntity($entityManager, $entityInstance);
    // }

    // public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    // {
    //     if (!$entityInstance instanceof Posts) {
    //         return;
    //     }

    //     $this->processPost($entityInstance, false);

    //     $message = new TriggerNextJsBuild('Build');
    //     $this->messageBus->dispatch($message);

    //     parent::updateEntity($entityManager, $entityInstance);
    // }

    // private function processPost(Posts $post, bool $isNew): void
    // {
    //     if (empty($post->getSlug()) && $post->getSlug() !== 'Accueil') {
    //         $slug = $this->createSlug($post->getTitle());
    //         $post->setSlug($slug);
    //     }

    //     $slug = $post->getSlug();

    //     if ($slug !== 'Accueil') {
    //         $categorySlug = $post->getCategory() ? $post->getCategory()->getSlug() : null;
    //         $subcategorySlug = $post->getSubcategory() ? $post->getSubcategory()->getSlug() : null;
    //         $url = $this->urlGeneratorService->generatePath($slug, $categorySlug, $subcategorySlug);
    //         $post->setUrl($url);
    //     } else {
    //         $post->setUrl('');
    //     }

    //     if (empty($post->getAltImg())) {
    //         $post->setAltImg($post->getTitle());
    //     }

    //     if (!empty($post->getContents())) {
    //         $markdownText = $post->getContents();
    //         $htmlText = $this->markdownProcessor->processMarkdown($markdownText);

    //         $dom = new DOMDocument();
    //         @$dom->loadHTML(
    //             mb_convert_encoding($htmlText, 'HTML-ENTITIES', 'UTF-8'),
    //             LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    //         );
    //         $images = $dom->getElementsByTagName('img');

    //         foreach ($images as $image) {
    //             $image->setAttribute('loading', 'lazy');
    //         }

    //         $htmlTextWithLazyLoading = $dom->saveHTML();
    //         $post->setContents($htmlTextWithLazyLoading);
    //     }

    //     $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'dd MMMM yyyy');

    //     if ($isNew) {
    //         $post->setCreatedAt(new GlobalDateTime());
    //         $createdAt = $formatter->format($post->getCreatedAt());
    //         $post->setFormattedDate('Publié le ' . $createdAt);
    //     } else {
    //         $post->setUpdatedAt(new GlobalDateTime());
    //         $updatedDate = $formatter->format($post->getUpdatedAt());
    //         $createdAt = $formatter->format($post->getCreatedAt());
    //         $post->setFormattedDate('Publié le ' . $createdAt . '. Mise à jour le ' . $updatedDate);
    //     }

    //     foreach ($post->getParagraphPosts() as $paragraph) {
    //         $this->processParagraph($paragraph, $post);
    //     }
    // }

    // private function processParagraph(ParagraphPosts $paragraph, Posts $post): void
    // {
    //     if (!empty($paragraph->getParagraph())) {
    //         $markdownText = $paragraph->getParagraph();
    //         $htmlText = $this->markdownProcessor->processMarkdown($markdownText);

    //         $dom = new DOMDocument('1.0', 'UTF-8');
    //         @$dom->loadHTML(
    //             mb_convert_encoding($htmlText, 'HTML-ENTITIES', 'UTF-8'),
    //             LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    //         );

    //         $images = $dom->getElementsByTagName('img');
    //         foreach ($images as $image) {
    //             $image->setAttribute('loading', 'lazy');
    //         }

    //         $htmlTextWithLazyLoading = $dom->saveHTML();
    //         $paragraph->setParagraph($htmlTextWithLazyLoading);
    //     }

    //     if (!empty($paragraph->getSubtitle())) {
    //         $slugPara = $this->createSlug($paragraph->getSubtitle());
    //         $slugPara = substr($slugPara, 0, 30);
    //         $paragraph->setSlug($slugPara);

    //         $categoryLink = $post->getCategory() ? $post->getCategory()->getSlug() : null;
    //         if ($categoryLink === 'Pages') {
    //             $paragraph->setLinkSubtitle('/' . $slugPara);
    //         } else {
    //             $paragraph->setLinkSubtitle('/' . $categoryLink . '/' . $slugPara);
    //         }
    //     } else {
    //         $this->entityManager->remove($paragraph);
    //     }

  //     if (empty($paragraph->getAltImg()) && !empty($paragraph->getSubtitle())) {
    //         $paragraph->setAltImg($paragraph->getSubtitle());
    //     }
    // }

    // private function createSlug(string $inputString): string
    // {
    //     return strtolower($this->slugger->slug($inputString)->slice(0, 50)->toString());
    // }
}
