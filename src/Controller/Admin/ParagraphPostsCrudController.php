<?php

namespace App\Controller\Admin;

use App\Entity\ParagraphPosts;
use App\Service\MarkdownProcessor;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use DOMDocument;

class ParagraphPostsCrudController extends AbstractCrudController
{
    private SluggerInterface $slugger;
    private MarkdownProcessor $markdownProcessor;

    public function __construct(
        SluggerInterface $slugger,
        MarkdownProcessor $markdownProcessor
    ) {
        $this->slugger = $slugger;
        $this->markdownProcessor = $markdownProcessor;
    }

    public static function getEntityFqcn(): string
    {
        return ParagraphPosts::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Paragraphe')
            ->setEntityLabelInPlural('Paragraphes')
            ->setSearchFields(['subtitle', 'slug'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield AssociationField::new('posts', 'Post')
            ->setRequired(true)
            ->setColumns(12);

        yield TextField::new('subtitle', 'Sous-titre')
            ->setHelp('Max 170 caractères')
            ->setColumns(12);

        yield TextareaField::new('paragraph', 'Contenu (Markdown)')
            ->setHelp('Utilisez le format Markdown, sera converti en HTML automatiquement')
            ->setColumns(12);

        yield TextField::new('imgPostParagh', 'Image du paragraphe')
            ->hideOnIndex()
            ->setColumns(6);

        yield TextField::new('altImg', 'Alt Image')
            ->hideOnIndex()
            ->setColumns(6);

        yield TextField::new('slug', 'Slug')
            ->hideOnForm()
            ->setColumns(6);

        yield TextField::new('linkSubtitle', 'Lien généré')
            ->hideOnForm()
            ->setColumns(6);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof ParagraphPosts) {
            return;
        }

        $this->processParagraph($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof ParagraphPosts) {
            return;
        }

        $this->processParagraph($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function processParagraph(ParagraphPosts $paragraph): void
    {
        // MARKDOWN TO HTML
        if (!empty($paragraph->getParagraph())) {
            $markdownText = $paragraph->getParagraph();
            $htmlText = $this->markdownProcessor->processMarkdown($markdownText);

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

        // SLUG Generation for paragraph
        if (!empty($paragraph->getSubtitle())) {
            $slugPara = $this->createSlug($paragraph->getSubtitle());
            $slugPara = substr($slugPara, 0, 30);
            $paragraph->setSlug($slugPara);

            // Generate link if post is attached
            if ($paragraph->getPosts()) {
                $categoryLink = $paragraph->getPosts()->getCategory() ? $paragraph->getPosts()->getCategory()->getSlug() : null;
                if ($categoryLink === 'Pages') {
                    $paragraph->setLinkSubtitle('/' . $slugPara);
                } else {
                    $paragraph->setLinkSubtitle('/' . $categoryLink . '/' . $slugPara);
                }
            }
        }

        // ALT IMG for paragraph
        if (empty($paragraph->getAltImg()) && !empty($paragraph->getSubtitle())) {
            $paragraph->setAltImg($paragraph->getSubtitle());
        }
    }

    private function createSlug(string $inputString): string
    {
        return strtolower($this->slugger->slug($inputString)->slice(0, 50)->toString());
    }
}
