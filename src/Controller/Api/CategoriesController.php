<?php

namespace App\Controller\Api;

use App\Entity\Categories;
use App\Entity\Experiences;
use App\Repository\CategoriesRepository;
use App\Repository\ExperiencesRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * @Route("/api/categories",name="api_categories_")
 */
class CategoriesController extends ApiController
{
    /**
     * @Route("", name="browse", methods={"GET"})
     */
    public function browse(CategoriesRepository $categoriesRepository ): JsonResponse
    {



        $allCategories = $categoriesRepository->findAll();

        return $this->json(
            $allCategories,
            Response::HTTP_OK,
            [],
            [
                "groups" => 
                [
                    "api_categories_browse"
                ]
            ]
        );
    }

   

}
