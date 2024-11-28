<?php 
namespace App\Service;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class ImageOptimizer
{
    private $slugger;
    private $params;
    private $serializer;
    private $photoDir;
    private $projectDir;
    private $imagine;
    private $cloudinary;
    private $uploadApi;

    public function __construct(
        SluggerInterface $slugger,
        ContainerBagInterface $params,
        SerializerInterface $serializer,
        )
        {      
            $this->slugger = $slugger;
            $this->params = $params;
            $this->serializer = $serializer;
            $this->photoDir =  $this->params->get('app.imgDir');
            $this->projectDir =  $this->params->get('app.projectDir');
            $this->imagine = new Imagine();
            $this->uploadApi = Configuration::instance();
            $this->uploadApi->cloud->cloudName = $_ENV['CLOUD_NAME'];
            $this->uploadApi->cloud->apiKey = $_ENV['CLOUD_API_KEY'];
            $this->uploadApi->cloud->apiSecret = $_ENV['CLOUD_API_SECRET'];
            $this->uploadApi->url->secure = true;
            $this->uploadApi = new UploadApi();
    }

    public function setPicture( $brochureFile, $slug ): void
    {       



    

        $temporaryPath = $this->photoDir . $slug . '.' . $brochureFile->getClientOriginalExtension();

        $brochureFile->move($this->photoDir, $slug . '.' . $brochureFile->getClientOriginalExtension());

        $mimeType = mime_content_type($temporaryPath);

        // Si le fichier est un GIF, le télécharger directement sans conversion
        if ($mimeType === 'image/gif') {
            // Upload directement sur Cloudinary
            $this->uploadApi->upload($temporaryPath, array(
                 "public_id" => $slug,
                "folder" => "portfolio",
                "overwrite" => true,
                "resource_type" => "auto",
                "quality" => "auto",
                "fetch_format" => "gif",
                "width" => 1280,
                "height" => 1080,
                "crop" => "limit",
                "secure" => true)
            );
            
            if (file_exists($temporaryPath)) {
            unlink($temporaryPath);
            } else {
                throw new \Exception("Fichier temporaire introuvable : " . $temporaryPath);
            }

            return; 
        }

        $localFilePath = $this->photoDir . $slug . '.webp';

        // Save Local File
        $this->imagine->open($brochureFile)
            ->thumbnail(new Box(1280, 1080))
            ->save($localFilePath, ['webp_quality' => 100]);

        // Save Cloudinary File

        $this->uploadApi->upload($this->photoDir.$slug.'.webp', array(
            "public_id" => $slug,
            "folder" => "portfolio",
            "overwrite" => true,
            "resource_type" => "auto",
            "quality" => "auto",
            "fetch_format" => "webp",
            "width" => 1280,
            "height" => 1080,
            "crop" => "limit",
            "secure" => true)
        );
         if (file_exists($localFilePath)) {
        unlink($localFilePath);
        } else {
            throw new \Exception("Fichier temporaire introuvable : " . $localFilePath);
        }
    }
        
}



