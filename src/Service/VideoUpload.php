<?php
namespace App\Service;

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


class VideoUpload
{

    private $photoDir;
    private $params;
    private $projectDir;
    private $cloudinary;
    private $uploadApi;

    public function __construct(
        ContainerBagInterface $params,
    )
    {
        $this->params = $params;
        $this->projectDir =  $this->params->get('app.projectDir');
        $this->photoDir =  $this->params->get('app.imgDir');
        $this->uploadApi = Configuration::instance();
        $this->uploadApi->cloud->cloudName = $_ENV['CLOUD_NAME'];
        $this->uploadApi->cloud->apiKey = $_ENV['CLOUD_API_KEY'];
        $this->uploadApi->cloud->apiSecret = $_ENV['CLOUD_API_SECRET'];
        $this->uploadApi->url->secure = true;
        $this->uploadApi = new UploadApi();
    }


    public function setVideo(  $file, $slug ): void
    {   

        // Save Cloudinary File
        $this->uploadApi->upload($file, [
            'resource_type' => 'video',
            'public_id' => $_ENV['CLOUD_FOLDER'].'/'.$slug,
        ]
            );
        }
}



