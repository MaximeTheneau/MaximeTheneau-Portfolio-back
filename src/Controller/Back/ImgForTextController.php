<?php 

namespace App\Controller\Back;

use App\Entity\Posts;
use App\Service\ImageOptimizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImgForTextController extends AbstractController
{
    private $imageOptimizer;

    public function __construct(ImageOptimizer $imageOptimizer)
    {
        $this->imageOptimizer = $imageOptimizer;
    }

    #[Route('/uploadImg', name: 'image_upload', methods: [ 'POST'])]
    public function uploadImg(Request $request, EntityManagerInterface $entityManager ): JsonResponse
    {
        $file = $request->files->get('upload');
        $id = $request->query->get('id');
        $paragraph = $request->query->get('paragraph');

        $post = $entityManager->getRepository(Posts::class)->find($id);

        if ($file) {
            $slug = $post->getSlug();
            
            $this->imageOptimizer->setPicture($file, $post , $slug);

            // Retourner l'URL de l'image téléchargée
            $url = 'https://res.cloudinary.com/' . $_ENV['CLOUD_NAME'] . '/image/upload/portfolio/' . $slug . '.webp';

            return new JsonResponse(['url' => $post->getImgPost()]);
        }

        return new JsonResponse(['error' => 'Aucun fichier téléchargé'], 400);
    }
}
