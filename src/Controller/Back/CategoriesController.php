<?php

namespace App\Controller\Back;

use App\Entity\Categories;
use App\Form\CategoriesType;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

/**
 * 
 * @link https://symfony.com/bundles/SensioFrameworkExtraBundle/current/annotations/security.html#isgranted
 * @IsGranted("ROLE_MANAGER")
 * 
 * @Route("/back/categories")
 */
class CategoriesController extends AbstractController
{
    /**
     * @Route("", name="app_back_categories_index", methods={"GET"})
     */
    public function index(CategoriesRepository $categoriesRepository): Response
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('back/categories/index.html.twig', [
            'categories' => $categoriesRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_back_categories_new", methods={"GET", "POST"})
     */
    public function new(Request $request, CategoriesRepository $categoriesRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categories = new Categories();
        $form = $this->createForm(categoriesType::class, $categories);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $categoriesRepository->add($categories, true);

            $this->addFlash('success', 'Categories ajouté(e).');

            return $this->redirectToRoute('app_back_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/categories/new.html.twig', [
            'categories' => $categories,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_categories_categories_show", methods={"GET"})
     */
    public function show(Categories $categories): Response
    {
        return $this->render('back/categories/show.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_back_categories_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Categories $categories, categoriesRepository $categoriesRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // $this->denyAccessUnlessGranted('MOVIE_EDIT_1400', $categories);

        $form = $this->createForm(categoriesType::class, $categories);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $categoriesRepository->add($categories, true);

            $this->addFlash('success', 'Film ou série modifié(e).');
            return $this->redirectToRoute('app_back_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/categories/edit.html.twig', [
            'categories' => $categories,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_back_categories_delete", methods={"POST"})
     */
    public function delete(Request $request, categories $categories, categoriesRepository $categoriesRepository): Response
    {

        if ($this->isCsrfTokenValid('delete'.$categories->getId(), $request->request->get('_token'))) {
            $categoriesRepository->remove($categories, true);

            $this->addFlash('success', $categories->getTitle() . ', supprimé.');
        }

        return $this->redirectToRoute('app_back_categories_index', [], Response::HTTP_SEE_OTHER);
    }
}
