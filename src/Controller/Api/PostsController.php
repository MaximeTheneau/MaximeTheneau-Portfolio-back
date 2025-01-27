<?php

namespace App\Controller\Api;

use App\Entity\Posts;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Skill;
use App\Entity\Subcategory;
use App\Repository\PostsRepository;
use App\Repository\SubcategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use App\Service\MarkdownProcessor;

#[Route('/api/posts', name: 'api_posts_')]
class PostsController extends ApiController
{
    private $markdownProcessor;

    public function __construct(
        MarkdownProcessor $markdownProcessor,
    ) {
        $this->markdownProcessor = $markdownProcessor;
    }
	

    #[Route('/home', name: 'home', methods: ['GET'])]
    public function browse(PostsRepository $postsRepository, EntityManagerInterface $em ): JsonResponse
    {
    
        $posts = $postsRepository->findOneBy(['slug'=> 'Accueil']);
        $category = $em->getRepository(Category::class)->findByName('Creations');
        $creation = $em->getRepository(Posts::class)->findBy(['category' => $category, 'isHomeImage' => true], ['createdAt' => 'DESC'], 3);
        $skills = $em->getRepository(Skill::class)->findAll();

        $faq = $postsRepository->findOneBy(['slug'=> 'Foire-aux-questions']);
        $products = $em->getRepository(Product::class)->findAll();

        $allPosts = [
            'home' => $posts,
            'products' => $products,
            'creation'=> $creation,
            'faq' => $faq->getListPosts()->slice(0, 3),
            'skills' => $skills
        ];

        return $this->json(
            $allPosts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_home"
                ]
            ]
        );
    }

    #[Route('&category={name}', name: 'category', methods: ['GET'])]  
    public function category(EntityManagerInterface $em , string $name): JsonResponse
    {
        $category = $em->getRepository(Category::class)->findByName($name);

        if (!$category) {
            return $this->json(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $posts = $em->getRepository(Posts::class)->findBy(['category' => $category], ['createdAt' => 'DESC']);

        return $this->json(
            $posts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_category"

                ]
            ]
        );
        }

    #[Route('&subcategory={slug}', name: 'subcategory', methods: ['GET'])]
    public function subcategory(EntityManagerInterface $em, string $slug): JsonResponse
    {
        
        $subcategory = $em->getRepository(Subcategory::class)->findBySlug($slug);
        $posts = $em->getRepository(Posts::class)->findBy(['subcategory' => $subcategory],  ['createdAt' => 'DESC']);

        return $this->json(
            $posts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_subcategory"
                ]
            ]
            );
    }
        
    

    #[Route('&limit=3&category={name}', name: 'limit', methods: ['GET'])]
    public function limit(EntityManagerInterface $em, string $name): JsonResponse
    {
        $category = $em->getRepository(Category::class)->findByName($name);

        if (!$category) {
            return $this->json(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $posts = $em->getRepository(Posts::class)->findBy(['category' => $category, 'isHomeImage' => true], ['createdAt' => 'DESC'], 3);

        return $this->json(
            $posts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_browse"

                ]
            ]
        );
    }
        
    #[Route('&limit=3&filter=desc&category={name}', name: 'desc', methods: ['GET'])]
    public function desc(PostsRepository $postsRepository, Category $category ): JsonResponse
    {

        $allPosts = $postsRepository->findDescPosts();

        return $this->json(
            $allPosts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_desc"
                ]
            ]
        );
    }

    #[Route('/all', name: 'all', methods: ['GET'])]
    public function all(PostsRepository $postsRepository ): JsonResponse
    {
    
        $allPosts = $postsRepository->findAllPosts();

        return $this->json(
            $allPosts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_read"
                ]
            ]
        );
    }

    #[Route('/thumbnail/{slug}', name: 'thumbnail', methods: ["GET"])]
    public function thumbnail(PostsRepository $postsRepository, Posts $posts = null ): JsonResponse
    {
    
        if ($posts === null)
        {
            // on renvoie donc une 404
            return $this->json(
                [
                    "erreur" => "Page non trouvée",
                    "code_error" => 404
                ],
                Response::HTTP_NOT_FOUND,// 404
            );
        }

        return $this->json(
            $posts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_thumbnail"
                ]
            ]
        );
    }

    #[Route('/sitemap', name: 'sitemap', methods: ['GET'])]
    public function site(PostsRepository $postsRepository ): JsonResponse
    {
        $allPosts = $postsRepository->findAllPosts();

        return $this->json(
            $allPosts,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_sitemap"
                ]
            ]
        );
    }

    #[Route('/{slug}', name: 'read', methods: ['GET'])]
    public function read(EntityManagerInterface $em, string $slug )
    {
        $post = $em->getRepository(Posts::class)->findOneBy(['slug' => $slug]);
        $latestPosts = $em->getRepository(Posts::class)->findBy([], ['createdAt' => 'DESC'], 3);

        if ($post === null)
        {
            // on renvoie donc une 404
            return $this->json(
                [
                    "erreur" => "Page non trouvée",
                    "code_error" => 404
                ],
                Response::HTTP_NOT_FOUND,// 404
            );
        }
        $relatedPosts = $post->getRelatedPosts();

            // Si vous voulez seulement certaines données des posts associés, vous pouvez mapper les entités en un tableau d'objets plus simples
            $relatedPostsData = [];
            foreach ($relatedPosts as $relatedPost) {
                $relatedPostsData[] = [
                    'id' => $relatedPost->getId(),
                    'slug' => $relatedPost->getSlug(),
                    'title' => $relatedPost->getTitle(),
                    'altImg' => $relatedPost->getAltImg(),
                    'url' => $relatedPost->getUrl(),
                    'imgPost' => $relatedPost->getImgPost(),

                ];
            }

            $latestPostsData = [];
            foreach ($latestPosts as $latestPost) {
                $latestPostsData[] = [
                    'id' => $latestPost->getId(),
                    'slug' => $latestPost->getSlug(),
                    'title' => $latestPost->getTitle(),
                    'url' => $latestPost->getUrl(),
                    'formattedDate' => $latestPost->getFormattedDate(),

                ];
            }
        return $this->json(
            [
                'post' => $post,
                'latestPosts' => $latestPostsData,
                'relatedPosts' => $relatedPostsData
            ],
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_read",
                ]
            ]);
    }

     #[Route('/related/{slug}', name: 'related', methods: ['GET'])]
    public function relatedPosts(EntityManagerInterface $em, string $slug )
    {
        $post = $em->getRepository(Posts::class)->findOneBy(['slug' => $slug]);

        if ($post === null)
        {
            // on renvoie donc une 404
            return $this->json(
                [
                    "erreur" => "Page non trouvée",
                    "code_error" => 404
                ],
                Response::HTTP_NOT_FOUND,// 404
            );
        }
    $relatedPosts = $post->getRelatedPosts();

    // Si vous voulez seulement certaines données des posts associés, vous pouvez mapper les entités en un tableau d'objets plus simples
    $relatedPostsData = [];
    foreach ($relatedPosts as $relatedPost) {
        $relatedPostsData[] = [
            'id' => $relatedPost->getId(),
            'slug' => $relatedPost->getSlug(),
            'title' => $relatedPost->getTitle(),
            'altImg' => $relatedPost->getAltImg(),
            'url' => $relatedPost->getUrl(),
            'imgPost' => $relatedPost->getImgPost(),

        ];
    }

    // Retourner la réponse JSON avec les posts associés
    return $this->json(
        $relatedPostsData,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_posts_related",
                ]
            ]);
    }

    #[Route('&filter=subcategory', name: 'allSubcategory', methods: ['GET'])]
    public function allSubcategory(SubcategoryRepository $subcategories ): JsonResponse
    {
    
        $subcategories = $subcategories->findAll();

        return $this->json(
            $subcategories,
            Response::HTTP_OK,
            [],
            [
                "groups" => ["api_posts__allSubcategory"]
            ]
        );
    }



}
