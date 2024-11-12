<?php

namespace App\Controller\Back;

use App\Entity\Product;
use App\Entity\ProductOption;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\MarkdownProcessor;

#[Route('/product')]
final class ProductController extends AbstractController
{
    private $markdownProcessor;

    public function __construct(
        MarkdownProcessor $markdownProcessor,
        ){
        $this->markdownProcessor = $markdownProcessor;

    }

    #[Route(name: 'app_back_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('back/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_back_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // MARKDOWN TO HTML
            $markdownText = $product->getDescription();

            $htmlText = $this->markdownProcessor->processMarkdown($markdownText);

            $product->setDescription($htmlText);
            $entityManager->persist($product);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_back_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('back/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_back_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setDiscountedPrice($product->getPrice() * 0.5);

            $productPosts = $form->get('productOptions')->getData();

            foreach ($productPosts as $product) {
                // MARKDOWN TO HTML
                $markdownText = $product->getLabel();

                $htmlText = $this->markdownProcessor->processMarkdown($markdownText);

                $product->setLabel($htmlText);
                
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_back_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {        
        $productOptions = $entityManager->getRepository(ProductOption::class)->findBy(['product' => $product]);

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            
            foreach ($productOptions as $option) {
                $entityManager->remove($option);
            }
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_back_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
