<?php

namespace App\Controller\Back;

use App\Entity\Posts;
use App\Entity\Category;
use App\Entity\ListPosts;
use App\Entity\ParagraphPosts;
use App\Entity\Keyword;
use App\Form\PostsType;
use App\Form\ParagraphPostsType;
use App\Message\TriggerNextJsBuild;
use App\Repository\PostsRepository;
use App\Repository\CategoryRepository;
use App\Repository\ParagraphPostsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use DateTime;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use App\Service\ImageOptimizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Michelf\MarkdownExtra;
use \IntlDateFormatter;
use App\Service\MarkdownProcessor;
use App\Service\UrlGeneratorService;
use App\Message\UpdateNextAppMessage;
use Symfony\Component\String\UnicodeString;

#[Route('/posts')]
class PostsController extends AbstractController
{
    private $params;
    private $imageOptimizer;
    private $slugger;
    private $photoDir;
    private $projectDir;
    private $entityManager;
    private $markdownProcessor;
    private $messageBus;
    private $urlGeneratorService;
    
    public function __construct(
        ContainerBagInterface $params,
        ImageOptimizer $imageOptimizer,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        UrlGeneratorService $urlGeneratorService,
        MarkdownProcessor $markdownProcessor,
    )
    {
        $this->params = $params;
        $this->entityManager = $entityManager;
        $this->imageOptimizer = $imageOptimizer;
        $this->slugger = $slugger;
        $this->projectDir =  $this->params->get('app.projectDir');
        $this->photoDir =  $this->params->get('app.imgDir');
        $this->messageBus = $messageBus;
        $this->urlGeneratorService = $urlGeneratorService;
        $this->markdownProcessor = $markdownProcessor;
    }
    private function createSlug(string $inputString): string
    {
        return strtolower($this->slugger->slug($inputString)->slice(0, 50)->toString());
    }
    #[Route('/', name: 'app_back_posts_index', methods: ['GET'])]
    public function index(PostsRepository $postsRepository, Request $request ): Response
    {
        $error = $request->query->get('error');
        $posts = $postsRepository->findAll();
    
        return $this->render('back/posts/index.html.twig', [
            'posts' => $posts,
            'error' => $error,
        ]);
    }

    #[Route('/category/{name}', name: 'app_back_posts_list', methods: ['GET'])]
    public function categoryPage(PostsRepository $postsRepository, Category $category): Response
    {
        $posts = $postsRepository->findBy(['category' => $category]);
        return $this->render('back/posts/index.html.twig', [
            'posts' => $posts,
            'category' => $category,
        ]);
    }

    #[Route('/new', name: 'app_back_posts_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PostsRepository $postsRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post = new Posts();

        $category = new Category();
        
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);
        
        
        if ($form->isSubmitted() && $form->isValid()) {

            // SLUG
            $slug = $this->createSlug($post->getTitle());
            if($post->getSlug() !== "Accueil") {
                $post->setSlug($slug);
                $categorySlug = $post->getCategory() ? $post->getCategory()->getSlug() : null;
                $subcategorySlug = $post->getSubcategory() ? $post->getSubcategory()->getSlug() : null;
            
                $url = $this->urlGeneratorService->generatePath($slug, $categorySlug, $subcategorySlug);
                $post->setUrl($url);
            } else {
                $post->setSlug('Accueil');
                $url = '';
                $post->setUrl($url);
            }

            // IMAGE Principal
            $brochureFile = $form->get('imgPost')->getData();
            if (empty($brochureFile)) {
                $post->setImgPost('Accueil');
                $post->setAltImg('Image de présentation');
            } else {
                $post->setImgPost($slug);
                $this->imageOptimizer->setPicture($brochureFile, $slug, $post );
                
            }

            // ALT IMG
            if (empty($post->getAltImg())) {
                $post->setAltImg($post->getTitle());
            } else {
                $post->setAltImg($post->getAltImg());
            }
            
            // DATE
            $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'dd MMMM yyyy');
            $post->setCreatedAt(new DateTime());
            $createdAt = $formatter->format($post->getCreatedAt());

            $post->setFormattedDate('Publié le ' . $createdAt);


            // PARAGRAPH
            $paragraphPosts = $form->get('paragraphPosts')->getData();
            foreach ($paragraphPosts as $paragraph) {
                
                // MARKDOWN TO HTML
                $markdownText = $paragraph->getParagraph();

                $htmlText = $this->markdownProcessor->processMarkdown($markdownText);

                $paragraph->setParagraph($htmlText);

                // SLUG
                if (!empty($paragraph->getSubtitle())) {
                    $slugPara = $this->createSlug($paragraph->getSubtitle());
                    $slugPara = substr($slugPara, 0, 30); 
                    $paragraph->setSlug($slugPara);
                    $categoryLink = $post->getCategory()->getSlug();
                    if ($categoryLink === "Pages") {
                        $paragraph->setLinkSubtitle('/' . $slugPara);
                    } else {
                        $paragraph->setLinkSubtitle('/' . $categoryLink . '/' . $slugPara);
                    } 
                } else {
                    $this->entityManager->remove($paragraph);
                    $this->entityManager->flush();
                }

                //  // IMAGE PARAGRAPH

                //  $imgPostParaghFile = $paragraph->getImgPostParaghFile();

                //  if ($imgPostParaghFile !== null ) {
                //      $brochureFileParagraph = $paragraph->getImgPostParagh();
                //      // SLUG
                //      $slugPara = $this->slugger->slug($paragraph->getSubtitle()); // slugify
                //      $slugPara = substr($slugPara, 0, 30); // 30 max
                //      $paragraph->setImgPostParagh($slugPara);// set slug to image paragraph
                //      // Cloudinary
                //      $this->imageOptimizer->setPicture($brochureFileParagraph, $slugPara, $paragraph ); // set image paragraph
                //  } 
 
                //  // ALT IMG PARAGRAPH
                //  if (empty($paragraph->getAltImg())) {
                //      $paragraph->setAltImg($paragraph->getSubtitle());
                //  } else {
                //      $paragraph->setAltImg($paragraph->getAltImg());
                //  }          
            } 


            $postsRepository->save($post, true);

            return $this->redirectToRoute('app_back_posts_index', [
            ], Response::HTTP_SEE_OTHER);

        }
        return $this->render('back/posts/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_back_posts_show', methods: ['GET'])]
    public function show(Posts $post): Response
    {
        return $this->render('back/posts/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_back_posts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Posts $post, $id, ParagraphPostsRepository $paragraphPostsRepository, PostsRepository $postsRepository): Response
    {
        $imgPost = $post->getImgPost();
        
        $articles = $postsRepository->findAll();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);

        $paragraphPosts = $paragraphPostsRepository->find($id);
        
        
        $formParagraph = $this->createForm(ParagraphPostsType::class, $paragraphPosts);
        $formParagraph->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {

            // // SLUG
            $slug = $post->getSlug();
            
            // if($post->getSlug() !== "Accueil") {
            //     $post->setSlug($slug);
                $categorySlug = $post->getCategory() ? $post->getCategory()->getSlug() : null;
                $subcategorySlug = $post->getSubcategory() ? $post->getSubcategory()->getSlug() : null;
            
                $url = $this->urlGeneratorService->generatePath($slug, $categorySlug, $subcategorySlug);
                $post->setUrl($url);
            // } else {
            //     $post->setSlug('Accueil');
            //     $url = '/';
            //     $post->setUrl($url);
            // }
            
            // IMAGE Principal
            $brochureFile = $form->get('imgPost')->getData();

            if (!empty($brochureFile)) {
                
                $post->setImgPost($slug);
                $this->imageOptimizer->setPicture($brochureFile, $post->getImgPost(), $post );
                
            } else {
                $post->setImgPost($imgPost);
            }
            
            // DATE
            $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'dd MMMM yyyy');
            $post->setUpdatedAt(new DateTime());
            $updatedDate = $formatter->format($post->getUpdatedAt());
            $createdAt = $formatter->format($post->getCreatedAt());

            $post->setFormattedDate('Publié le ' . $createdAt . '. Mise à jour le ' . $updatedDate);

            $postsRepository->save($post, true);

            // PARAGRAPH
            $paragraphPosts = $form->get('paragraphPosts')->getData();

            foreach ($paragraphPosts as $paragraph) {

                // MARKDOWN TO HTML
                $markdownText = $paragraph->getParagraph();

                $htmlText = $this->markdownProcessor->processMarkdown($markdownText);

                $paragraph->setParagraph($htmlText);
                
                // SLUG
                if (!empty($paragraph->getSubtitle())) {
                    $slugPara = $this->createSlug($paragraph->getSubtitle());
                    $slugPara = substr($slugPara, 0, 30); 
                    $paragraph->setSlug($slugPara);
                    $categoryLink = $post->getCategory()->getSlug();
                    if ($categoryLink === "Pages") {
                        $paragraph->setLinkSubtitle('/' . $slugPara);
                    } else {
                        $paragraph->setLinkSubtitle('/' . $categoryLink . '/' . $slugPara);
                    } 
                } else {
                    $this->entityManager->remove($paragraph);
                    $this->entityManager->flush();
                }
                
                // // LINK
                // $articleLink = $paragraph->getLinkPostSelect();
                // if ($articleLink !== null) {
                    
                //     $paragraph->setLinkSubtitle($articleLink->getTitle());
                //     $slugLink = $articleLink->getSlug();

                //     $categoryLink = $articleLink->getCategory()->getSlug();
                //     if ($categoryLink === "Pages") {
                //         $paragraph->setLink('/'.$slugLink);
                //     }                     
                //     if ($categoryLink === "Annuaire") {
                //         $paragraph->setLink('/'.$categoryLink.'/'.$slugLink);
                //     } 
                //     if ($categoryLink === "Articles") {
                //         $subcategoryLink = $articleLink->getSubcategory()->getSlug();
                //         $paragraph->setLink('/'.$categoryLink.'/'.$subcategoryLink.'/'.$slugLink);
                //     }
                // } 

              
                
                // $deletedLink = $form['paragraphPosts'];

                // if ($deletedLink[$paragraphPosts->indexOf($paragraph)]['deleteLink']->getData() === true) {
                //     $paragraph->setLink(null);
                //     $paragraph->setLinkSubtitle(null);
                // }


                // IMAGE PARAGRAPH
                // if (!empty($paragraph->getImgPostParaghFile())) {
                //     $brochureFileParagraph = $paragraph->getImgPostParaghFile();
                //     $slugPara = $this->slugger->slug($paragraph->getSubtitle());
                //     $slugPara = substr($slugPara, 0, 30);
                //     $paragraph->setImgPostParagh($slugPara);
                //     $this->imageOptimizer->setPicture($brochureFileParagraph, $slugPara, $paragraph);
                    
                //     // ALT IMG PARAGRAPH
                //     if (empty($paragraph->getAltImg())) {
                //         $paragraph->setAltImg($paragraph->getSubtitle());
                //     } 
                // }
            } 
            // $listPosts = $post->getListPosts();
            // if ($listPosts !== null) {
            //     foreach ($listPosts as $listPost) {
            //         if ($listPost->getLinkPostSelect() !== null){

            //             $listPost->setLinkSubtitle($listPost->getLinkPostSelect()->getTitle());
            //             $listPost->setLink($listPost->getLinkPostSelect()->getUrl());
            //         }
            //     }
            // }
            // DATE
            $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'dd MMMM yyyy');
            $post->setUpdatedAt(new DateTime());
            $updatedDate = $formatter->format($post->getUpdatedAt());
            $createdAt = $formatter->format($post->getCreatedAt());

            $message = new TriggerNextJsBuild('Build');
            $this->messageBus->dispatch($message);
            
            $postsRepository->save($post, true);
            

            return $this->redirectToRoute('app_back_posts_index', [
                // 'error' => $response->getContent(),
            ], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('back/posts/edit.html.twig', [
            'post' => $post,
            'form' => $form,
            'articles' => $articles,
        ]);
    }

    #[Route('/{id}', name: 'app_back_posts_delete', methods: ['POST'])]
    public function delete(Request $request, Posts $post, PostsRepository $postsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $postsRepository->remove($post, true);
        }

        return $this->redirectToRoute('app_back_posts_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/deleted', name: 'app_back_posts_paragraph_deleted', methods: ['GET', 'POST'])]
    public function deleteParagraph(Request $request, $id, PostsRepository $postsRepository, ParagraphPosts $paragraphPosts, ParagraphPostsRepository $paragraphPostsRepository): Response
    {

        $paragraph = $paragraphPostsRepository->find($id);

        $post = $postsRepository->find($id);
        $postId = $paragraph->getPosts()->getId();
        if ($this->isCsrfTokenValid('delete' . $paragraph->getId(), $request->request->get('_token'))) {
                $paragraph->setLink(null);
                $paragraph->setLinkSubtitle(null);

                $this->imageOptimizer->deletedPicture($slug);

                $this->entityManager->flush();
            
        }
        
        return $this->redirectToRoute('app_back_posts_edit', ['id' => $postId], Response::HTTP_SEE_OTHER);
    }

     #[Route('/gpt/save-data', name: 'save_data', methods: ['POST'])]
    public function saveParagraph(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $paragraph = $request->request->get('paragraph-id');

        if (empty($paragraph)) {
            return new JsonResponse(['error' => 'Le paragraph est vide'], 400);
        }

        // Sauvegarde en BDD
        $entity = $em->getRepository(ParagraphPosts::class)->findOneBy(['id' => $paragraph]);

        $entity->setParagraph($request->request->get('paragraph'));
        $em->persist($entity);
        $em->flush();

        // Retourne une réponse JSON
        return new JsonResponse(['success' => true, 'message' => 'Données enregistrées avec succès']);
    }
     #[Route('/gpt/save-data/posts', name: 'save_data', methods: ['POST'])]
    public function savePosts(Request $request, EntityManagerInterface $em): JsonResponse
    {   
        $data = $request->request->all();

        $post = $em->getRepository(Posts::class)->findOneBy(['title' => $data['posts']['title']]);

        foreach ($data['posts'] as $key => $value) {
                    if ($key === 'category' || $key === 'subcategory') {
            continue;  // Ignore la catégorie
        }
                $setter = 'set' . ucfirst($key); // On crée le nom du setter dynamiquement (par exemple 'setTitle')

                if (method_exists($post, $setter)) {
                    $post->$setter($value); // Appel du setter pour affecter la valeur à l'entité
                }
            }

        $em->persist($post);
        $em->flush();

        // Retourne une réponse JSON
        return new JsonResponse(['success' => true, 'message' => 'Données enregistrées avec succès']);
    }

    #[Route('/gpt/gpt-generate-paragraph', name: 'gpt_generate_paragraph', methods: ['POST'])]
    public function gptGenerateParagraph(Request $request): JsonResponse
    {
        // Récupérer le paramètre 'subtitle' envoyé via le corps de la requête
        $subtitle = $request->request->get('subtitle');

        if (!$subtitle) {
            return new JsonResponse(['error' => 'Rajouter une '], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {

            // $client = HttpClient::create();

            // $response = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            //     'headers' => [
            //         'Authorization' => 'Bearer ' . $_ENV['CHATGPT_API_KEY'],
            //         'Content-Type' => 'application/json',
            //     ],
            //     'json' => [
            //         'model' => 'gpt-4o-mini',
            //         'messages' => [
            //             [
            //                 'role' => 'user',
            //                 'content' => [
            //                     [
            //                         'type' => 'text',
            //                         'text' => "Génère un paragraphe en français basé sur ce sous-titre : \"$subtitle\"."
            //                     ],
            //                 ]
            //             ]
            //         ],
            //         'max_tokens' => 300,
            //         'temperature' => 0.7,
            //     ],
            // ]);


            // $data = $response->toArray();

            $content = "Ceci est un paragraphe généré pour tester l'API. Votre sous-titre était : \"$subtitle\".";


            $content = $this->markdownProcessor->processMarkdown($content);
            
            return $this->json([
                'message' => $content
            ]);
            // if (isset($data['choices']) && count($data['choices']) > 0) {

            // }


            return new JsonResponse(['content' => trim($content)]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error communicating with GPT: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/gpt/gpt-generate-posts', name: 'gpt_generate', methods: ['POST'])]
    public function gptGeneratePosts(Request $request, PostsRepository $postsRepository): JsonResponse
    {
        // Récupérer le paramètre 'subtitle' envoyé via le corps de la requête
        $subtitle = $request->request->get('subtitle');
        
        if (!$subtitle) {
            return new JsonResponse(['error' => 'Rajouter un Titre '], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $post = $this->entityManager->getRepository(Posts::class)->findOneBy(['heading' => $subtitle]);
        
        
        // try {

            $client = HttpClient::create();

            $content = $content = "Rédige un article structuré au format JSON suivant les spécifications ci-dessous :

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


            // $jsonContent = '{
            //     "title": "Comprendre",
            //     "heading": "Explorezqssqsq les nuances de l\'Accusantium en droit",
            //     "metaDescription": "Découvrez l\'Accusantium, ses enjeux et implications en droit.",
            //     "contents": "L\'Accusantium est un concept juridique complexe qui touche à des enjeux cruciaux. Cet article explore ses implications et comment il influence les décisions judiciaires.",
            //     "paragraphPosts": [
            //         {
            //             "subtitle": "Définitiffgonsqssq de l\'Accusantium",
            //             "paragraph": "L\'Accusantium se réfère à un ensemble de circonstances dans lesquelles une accusation est formulée. Il est essentiel de comprendre cette notion pour saisir les subtilités du droit pénal et les responsabilités qui en découlent."
            //         },
            //         {
            //             "subtitle": "Conséquencefgfgsqsqsqs Juridiques",
            //             "paragraph": "Les conséquences de l\'Accusantium peuvent être lourdes, tant pour l\'accusé que pour la société. En effet, une accusation mal fondée peut entraîner des répercussions graves, d\'où l\'importance d\'une compréhension approfondie de ce concept."
            //         }
            //     ]
            // }';
            $response = json_decode($jsonContent, true);


            if(!$post) {
                $post = new Posts();
                $post->setTitle($response['title']);
                $slug = $this->createSlug($subtitle);
            }
            $slug = $post->getSlug();
            
            if($post->getSlug() !== "Accueil") {
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
                $subtitle = $paragraph['subtitle'];
                $markdownText = $paragraph['paragraph'];
                
                $existingParagraph = $this->entityManager->getRepository(ParagraphPosts::class)->findOneBy(['subtitle' => $subtitle]);

                if (!$existingParagraph) {
                    $existingParagraph = new ParagraphPosts();
                    $post->addParagraphPost($existingParagraph);  // Ajouter le nouveau paragraphe
                } 

                $existingParagraph->setSubtitle($subtitle);

                // MARKDOWN TO HTML
                $htmlText = $this->markdownProcessor->processMarkdown($markdownText);
                $existingParagraph->setParagraph($htmlText);
                // SLUG
                if (!empty($existingParagraph->getSubtitle())) {
                    $slugPara = $this->createSlug($subtitle);
                    $slugPara = substr($slugPara, 0, 30); 
                    $existingParagraph->setSlug($slugPara);
                    $categoryLink = $post->getCategory()->getSlug();
                    if ($categoryLink === "Pages") {
                        $existingParagraph->setLinkSubtitle('/' . $slugPara);
                    } else {
                        $existingParagraph->setLinkSubtitle('/' . $categoryLink . '/' . $slugPara);
                } 
                
                $this->entityManager->persist($existingParagraph);
                } 
                    
                }
                $this->entityManager->persist($post);
                // Persister l'entité ParagraphPosts
                // $this->entityManager->persist($post);
                $this->entityManager->flush();


            return $this->json([
                'message' => true
            ]);

        // } catch (\Exception $e) {
        //     return new JsonResponse(['error' => 'Error communicating with GPT: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        // }
    }
}
