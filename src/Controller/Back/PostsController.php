<?php

namespace App\Controller\Back;

use App\Entity\Posts;
use App\Entity\Category;
use App\Entity\ListPosts;
use App\Entity\ParagraphPosts;
use App\Form\PostsType;
use App\Form\ParagraphPostsType;
use App\Repository\PostsRepository;
use App\Repository\CategoryRepository;
use App\Repository\ParagraphPostsRepository;
use App\Repository\LinkPostsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use App\Service\ImageOptimizer;
use App\Service\VideoUpload;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/posts')]
class PostsController extends AbstractController
{
    private $imageOptimizer;
    private $videoUpload;
    private $slugger;
    private $photoDir;
    private $params;
    private $projectDir;

    public function __construct(
        ContainerBagInterface $params,
        ImageOptimizer $imageOptimizer,
        SluggerInterface $slugger,
        VideoUpload $videoUpload
    )
    {
        $this->params = $params;
        $this->imageOptimizer = $imageOptimizer;
        $this->videoUpload = $videoUpload;
        $this->slugger = $slugger;
        $this->projectDir =  $this->params->get('app.projectDir');
        $this->photoDir =  $this->params->get('app.imgDir');
    }
    
    #[Route('/', name: 'app_back_posts_index', methods: ['GET'])]
    public function index(PostsRepository $postsRepository, CategoryRepository $categoryRepository): Response
    {
        return $this->render('back/posts/index.html.twig', [
            'posts' => $postsRepository->findAll(),
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/{name}', name: 'app_back_posts_list', methods: ['GET'])]
    public function categoryPage(PostsRepository $postsRepository, Category $category, CategoryRepository $categoryRepository): Response
    {
        $posts = $postsRepository->findBy(['category' => $category]);
    
        return $this->render('back/posts/index.html.twig', [
            'posts' => $posts,
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_back_posts_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PostsRepository $postsRepository, CategoryRepository $categoryRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post = new Posts();

        $category = new Category();


        
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);
        
        
        if ($form->isSubmitted() && $form->isValid()) {
            

            // SLUG
            $slug = $this->slugger->slug($post->getTitle());
            $post->setSlug($slug);

            // VIDEO
            $file = $form->get('video')->getData();
            if ($file instanceof UploadedFile) {
                $slugVideo = substr($slug, 0, 30); // 30 max
                $post->setVideo($slugVideo);

                $file->move($this->photoDir, $slugVideo.'.webm');


                $this->videoUpload->setVideo($this->photoDir.'/'.$slugVideo.'.webm', $slugVideo);
                
            } 

            // IMAGE
            $brochureFile = $form->get('imgPost')->getData();

            if ($brochureFile instanceof UploadedFile) {

                // SLUG
                $slugImg = substr($slug, 0, 30); // 30 max
                $post->setImgPost($slugImg);// set slug to image

                // Cloudinary
                $this->imageOptimizer->setPicture($brochureFile, $slugImg ); // set image
            }

            // ALT IMG
            if (empty($post->getAltImg())) {
                $post->setAltImg($post->getTitle());
            } else {
                $post->setAltImg($post->getAltImg());
            }
            
            // DATE
            $post->setCreatedAt(new DateTime());

            // IMAGE PARAGRAPH
            $brochureFileParagraph = $form->get('paragraphPosts')->getData();

            $paragraphPosts = $form->get('paragraphPosts')->getData();
            foreach ($paragraphPosts as $paragraph) {
                // IMAGE PARAGRAPH
                if (!empty($paragraph->getImgPostParagh())) {
                    $brochureFileParagraph = $paragraph->getImgPostParagh();
                    // SLUG
                    $slugPara = $this->slugger->slug($paragraph->getSubtitle()); // slugify
                    $slugPara = substr($slugPara, 0, 30); // 30 max
                    $paragraph->setImgPostParagh($slugPara);// set slug to image paragraph
                    // Cloudinary
                    $this->imageOptimizer->setPicture($brochureFileParagraph, $slugPara ); // set image paragraph
                } 

                // ALT IMG PARAGRAPH
                if (empty($paragraph->getAltImg())) {
                    $paragraph->setAltImg($paragraph->getSubtitle());
                } else {
                    $paragraph->setAltImg($paragraph->getAltImg());
                }          
            } 
            
            $postsRepository->save($post, true);

            return $this->redirectToRoute('app_back_posts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/posts/new.html.twig', [
            'post' => $post,
            'form' => $form,
            'categories' => $categoryRepository->findAll(),
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
    public function edit(Request $request, Posts $post, $id, ParagraphPostsRepository $paragraphPostsRepository, PostsRepository $postsRepository, CategoryRepository $categoryRepository): Response
    {
        $paragraphPosts = $paragraphPostsRepository->find($id);
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);


        $formParagraph = $this->createForm(ParagraphPostsType::class, $paragraphPosts);
        $formParagraph->handleRequest($request);
        $imgPost = $post->getImgPost();
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            // SLUG
            $slug = $this->slugger->slug($post->getTitle());
            $post->setSlug($slug);
            
            $image = $formParagraph->get('imgPostParagh')->getData();
    
            // VIDEO
            $file = $form->get('video')->getData();
            if ($file instanceof UploadedFile) {
                $slugVideo = substr($slug, 0, 30); // 30 max
                $post->setVideo($slugVideo);

                $file->move($this->photoDir, $slugVideo.'.webm');


                $this->videoUpload->setVideo($this->photoDir.'/'.$slugVideo.'.webm', $slugVideo);
                
            } 

            // IMAGE
            $brochureFile = $form->get('imgPost')->getData();

            if ($brochureFile instanceof UploadedFile) {

                // SLUG
                $slugImg = substr($slug, 0, 30); // 30 max
                $post->setImgPost($slugImg);// set slug to image
                
                // Cloudinary
                $this->imageOptimizer->setPicture($brochureFile, $slugImg ); // set image

                // ALT IMG
                 if ($form->get('altImg') === null) {
                     $post->setAltImg($form->get('altImg')->getData());
                 }
            }
            

           // IMAGE PARAGRAPH
            $brochureFileParagraph = $form->get('paragraphPosts')->getData();

            $paragraphPosts = $form->get('paragraphPosts')->getData();
            foreach ($paragraphPosts as $paragraph) {
                
                // IMAGE PARAGRAPH
                if (!empty($paragraph->getImgPostParagh())) {
                    $brochureFileParagraph = $paragraph->getImgPostParagh();
                    // Slug
                    $slugPara = $this->slugger->slug($paragraph->getSubtitle()); // slugify
                    $slugPara = substr($slugPara, 0, 30); // 30 max
                    $paragraph->setImgPostParagh($slugPara);// set slug to image paragraph
                    // Cloudinary
                    $this->imageOptimizer->setPicture($brochureFileParagraph, $slugPara ); // set image paragraph
                }
                // ALT IMG PARAGRAPH
                if (empty($paragraph->getAltImg())) {
                    $paragraph->setAltImg($paragraph->getSubtitle());
                } else {
                    $paragraph->setAltImg($paragraph->getAltImg());
                }
            } 

            $postsRepository->save($post, true);

            $response = new RedirectResponse($this->generateUrl('app_back_posts_index'), Response::HTTP_SEE_OTHER);
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            
            return $response;
        }

        return $this->renderForm('back/posts/edit.html.twig', [
            'post' => $post,
            'form' => $form,
            'categories' => $categoryRepository->findAll(),
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

    /**
     * @Route("/post/infinite-list", name="article_infinite_list")
     */
    public function infiniteList(Request $request): Response
    {
        
    }
}
