<?php

namespace App\Controller\Back;

use App\Entity\Subtopic;
use App\Form\SubtopicType;
use App\Repository\SubtopicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/subtopic')]
class SubtopicController extends AbstractController
{
    #[Route('/', name: 'app_back_subtopic_index', methods: ['GET'])]
    public function index(SubtopicRepository $subtopicRepository): Response
    {
        return $this->render('back/subtopic/index.html.twig', [
            'subtopics' => $subtopicRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_back_subtopic_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SubtopicRepository $subtopicRepository): Response
    {
        $subtopic = new Subtopic();
        $form = $this->createForm(SubtopicType::class, $subtopic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Create slug
            if(empty($subtopic->getSlug())) {
                $slug = $this->slugger->slug($subtopic->getName());
                $subtopic->setSlug($slug);
            }

            $subtopicRepository->save($subtopic, true);

            return $this->redirectToRoute('app_back_subtopic_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/subtopic/new.html.twig', [
            'subtopic' => $subtopic,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_subtopic_show', methods: ['GET'])]
    public function show(Subtopic $subtopic): Response
    {
        return $this->render('back/subtopic/show.html.twig', [
            'subtopic' => $subtopic,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_back_subtopic_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Subtopic $subtopic, SubtopicRepository $subtopicRepository): Response
    {
        $form = $this->createForm(SubtopicType::class, $subtopic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Create slug
            if($subtopic->getName() !== $form->get('name')->getData() ) {
                $slug = $this->slugger->slug($subtopic->getName());
                $subtopic->setSlug($slug);
            }
            if(empty($subtopic->getSlug())) {
                $slug = $this->slugger->slug($subtopic->getName());
                $subtopic->setSlug($slug);
            }

            $subtopicRepository->save($subtopic, true);

            return $this->redirectToRoute('app_back_subtopic_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/subtopic/edit.html.twig', [
            'subtopic' => $subtopic,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_subtopic_delete', methods: ['POST'])]
    public function delete(Request $request, Subtopic $subtopic, SubtopicRepository $subtopicRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subtopic->getId(), $request->request->get('_token'))) {
            $subtopicRepository->remove($subtopic, true);
        }

        return $this->redirectToRoute('app_back_subtopic_index', [], Response::HTTP_SEE_OTHER);
    }
}
