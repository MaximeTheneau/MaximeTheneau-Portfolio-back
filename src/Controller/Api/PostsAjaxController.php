<?php

namespace App\Controller\Api;

use App\Entity\Posts;
use App\Entity\ParagraphPosts;
use App\Service\MarkdownProcessor;
use App\Service\UrlGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use IntlDateFormatter;
use DateTime;

class PostsAjaxController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MarkdownProcessor $markdownProcessor;
    private UrlGeneratorService $urlGeneratorService;
    private SluggerInterface $slugger;

    public function __construct(
        EntityManagerInterface $entityManager,
        MarkdownProcessor $markdownProcessor,
        UrlGeneratorService $urlGeneratorService,
        SluggerInterface $slugger
    ) {
        $this->entityManager = $entityManager;
        $this->markdownProcessor = $markdownProcessor;
        $this->urlGeneratorService = $urlGeneratorService;
        $this->slugger = $slugger;
    }

    private function createSlug(string $inputString): string
    {
        return strtolower($this->slugger->slug($inputString)->slice(0, 50)->toString());
    }

    #[Route('/gpt/save-data', name: 'ajax_save_paragraph', methods: ['POST'])]
    public function saveParagraph(Request $request): JsonResponse
    {
        $paragraph = $request->request->get('paragraph-id');

        if (empty($paragraph)) {
            return new JsonResponse(['error' => 'Le paragraph est vide'], 400);
        }

        $entity = $this->entityManager->getRepository(ParagraphPosts::class)->findOneBy(['id' => $paragraph]);

        $entity->setParagraph($request->request->get('paragraph'));
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Données enregistrées avec succès']);
    }

    #[Route('/gpt/save-data/posts', name: 'ajax_save_posts', methods: ['POST'])]
    public function savePosts(Request $request): JsonResponse
    {
        $data = $request->request->all();

        $post = $this->entityManager->getRepository(Posts::class)->findOneBy(['title' => $data['posts']['title']]);

        foreach ($data['posts'] as $key => $value) {
            if ($key === 'category' || $key === 'subcategory') {
                continue;
            }
            $setter = 'set' . ucfirst($key);

            if (method_exists($post, $setter)) {
                $post->$setter($value);
            }
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Données enregistrées avec succès']);
    }

    #[Route('/gpt/gpt-generate-paragraph', name: 'ajax_gpt_generate_paragraph', methods: ['POST'])]
    public function gptGenerateParagraph(Request $request): JsonResponse
    {
        $subtitle = $request->request->get('subtitle');

        if (!$subtitle) {
            return new JsonResponse(['error' => 'Rajouter une '], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $content = "Ceci est un paragraphe généré pour tester l'API. Votre sous-titre était : \"$subtitle\".";
            $content = $this->markdownProcessor->processMarkdown($content);

            return $this->json([
                'message' => $content
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error communicating with GPT: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/gpt/gpt-generate-posts', name: 'ajax_gpt_generate_posts', methods: ['POST'])]
    public function gptGeneratePosts(Request $request): JsonResponse
    {
        $subtitle = $request->request->get('subtitle');

        if (!$subtitle) {
            return new JsonResponse(['error' => 'Rajouter un Titre '], JsonResponse::HTTP_BAD_REQUEST);
        }

        $post = $this->entityManager->getRepository(Posts::class)->findOneBy(['heading' => $subtitle]);

        $client = HttpClient::create();

        $content = "Rédige un article structuré au format JSON suivant les spécifications ci-dessous :

            - **title** : Un titre concis et captivant (max 60 caractères), incluant des mots-clés pertinents pour le sujet.
            - **heading** : Un en-tête décrivant brièvement le sujet de l'article (max 60 caractères).
            - **metaDescription** : Une description SEO optimisée pour les moteurs de recherche (max 135 caractères).
            - **contents** : Une introduction claire et engageante qui présente le sujet de l'article.
            - **paragraphPosts** : Une liste de sections, chacune incluant :
            - **subtitle** : Un sous-titre accrocheur et informatif.
            - **paragraph** : Un paragraphe détaillant le contenu sous le sous-titre.

            Le sujet de l'article est : \"$subtitle\". Respecte les limites de caractères et veille à ce que chaque champ soit bien structuré et adapté à une audience générale.

            Génère le contenu sous le format JSON strict suivant :

            ```json
            {
            \"title\": \"\",
            \"heading\": \"\",
            \"metaDescription\": \"\",
            \"contents\": \"\",
            \"paragraphPosts\": [
                {
                \"subtitle\": \"\",
                \"paragraph\": \"\"
                },
                {
                \"subtitle\": \"\",
                \"paragraph\": \"\"
                }
            ]
            }";

        $response = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV['CHATGPT_API_KEY'],
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $content
                            ],
                        ]
                    ]
                ],
                'max_tokens' => 2500,
                'temperature' => 0.7,
            ],
        ]);

        $data = $response->toArray();

        $responseJson = $data['choices'][0]['message']['content'];
        preg_match('/```json\n(.*?)\n```/s', $responseJson, $matches);
        $jsonContent = $matches[1];

        $response = json_decode($jsonContent, true);

        if (!$post) {
            $post = new Posts();
            $post->setTitle($response['title']);
            $slug = $this->createSlug($subtitle);
        }
        $slug = $post->getSlug();

        if ($post->getSlug() !== 'Accueil') {
            $post->setSlug($slug);
            $categorySlug = $post->getCategory() ? $post->getCategory()->getSlug() : null;
            $subcategorySlug = $post->getSubcategory() ? $post->getSubcategory()->getSlug() : null;

            $url = $this->urlGeneratorService->generatePath($slug, $categorySlug, $subcategorySlug);
            $post->setUrl($url);
        }

        $categorySlug = $post->getCategory() ? $post->getCategory()->getSlug() : null;
        $subcategorySlug = $post->getSubcategory() ? $post->getSubcategory()->getSlug() : null;
        $url = $this->urlGeneratorService->generatePath($slug, $categorySlug, $subcategorySlug);
        $post->setUrl($url);
        $post->setHeading($response['heading']);
        $post->setContents($response['contents']);
        $post->setMetaDescription($response['metaDescription']);

        // DATE
        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'dd MMMM yyyy');
        $post->setUpdatedAt(new DateTime());
        $updatedDate = $formatter->format($post->getUpdatedAt());
        $createdAt = $formatter->format($post->getCreatedAt());

        $post->setFormattedDate('Publié le ' . $createdAt . '. Mise à jour le ' . $updatedDate);

        foreach ($response['paragraphPosts'] as $paragraph) {
            $subtitlePara = $paragraph['subtitle'];
            $markdownText = $paragraph['paragraph'];

            $existingParagraph = $this->entityManager->getRepository(ParagraphPosts::class)->findOneBy(['subtitle' => $subtitlePara]);

            if (!$existingParagraph) {
                $existingParagraph = new ParagraphPosts();
                $post->addParagraphPost($existingParagraph);
            }

            $existingParagraph->setSubtitle($subtitlePara);

            // MARKDOWN TO HTML
            $htmlText = $this->markdownProcessor->processMarkdown($markdownText);
            $existingParagraph->setParagraph($htmlText);

            // SLUG
            if (!empty($existingParagraph->getSubtitle())) {
                $slugPara = $this->createSlug($subtitlePara);
                $slugPara = substr($slugPara, 0, 30);
                $existingParagraph->setSlug($slugPara);
                $categoryLink = $post->getCategory()->getSlug();
                if ($categoryLink === 'Pages') {
                    $existingParagraph->setLinkSubtitle('/' . $slugPara);
                } else {
                    $existingParagraph->setLinkSubtitle('/' . $categoryLink . '/' . $slugPara);
                }

                $this->entityManager->persist($existingParagraph);
            }
        }
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $this->json([
            'message' => true
        ]);
    }
}
