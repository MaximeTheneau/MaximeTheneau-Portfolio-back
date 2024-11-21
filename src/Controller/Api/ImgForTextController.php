<?php 

namespace App\Controller\Api;

use App\Service\ImageOptimizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImgForTextController extends ApiController
{
    private $imageOptimizer;

    public function __construct(ImageOptimizer $imageOptimizer)
    {
        $this->imageOptimizer = $imageOptimizer;
    }

    #[Route('/uploadImg', name: 'image_upload', methods: [ 'POST'])]
    public function uploadImg(Request $request): JsonResponse
    {
        $file = $request->files->get('upload');

        if ($file) {
            $slug = uniqid(); // Générer un ID unique pour l'image
            $this->imageOptimizer->setPicture($file, $slug);

            // Retourner l'URL de l'image téléchargée
            $url = 'https://res.cloudinary.com/' . $_ENV['CLOUD_NAME'] . '/image/upload/portfolio/' . $slug . '.webp';

            return new JsonResponse(['url' => $url]);
        }

        return new JsonResponse(['error' => 'Aucun fichier téléchargé'], 400);
    }
}
