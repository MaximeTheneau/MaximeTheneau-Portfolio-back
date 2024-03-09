<?php

namespace App\Controller\Back;

use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    #[Route('/', name: 'app_back_home', methods: ['GET'])]
    public function home(CategoryRepository $categoryRepository): Response
    {

        return $this->render('back/index.html.twig', [
        ]);
    }
        
}
