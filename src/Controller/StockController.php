<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Restaurant;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class StockController extends AbstractController
{
    #[Route('/restaurant/{id}/stock', name: 'app_stock_index')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(Restaurant $restaurant, StockRepository $stockRepository): Response
    {
        return $this->render('stock/index.html.twig', [
            'restaurant' => $restaurant,
            'stocks' => $stockRepository->findBy(['restaurant' => $restaurant]),
        ]);
    }

    #[Route('/restaurant/{id}/stock/create', name: 'app_stock_create')]
    public function create(Restaurant $restaurant, Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->add('quantity', IntegerType::class, [
            'label' => 'Quantité',
            'mapped' => false,
            'attr' => ['min' => 0],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);

            $stock = new Stock();
            $stock->setProduct($product);
            $stock->setRestaurant($restaurant);
            $stock->setQuantity($form->get('quantity')->getData());

            $entityManager->persist($stock);
            $entityManager->flush();

            return $this->redirectToRoute('app_stock_index', ['id' => $restaurant->getId()]);
        }

        return $this->render('stock/create.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/restaurant/{id}/stock/update', name: 'app_stock_update')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(Restaurant $restaurant, Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        $stock = $entityManager->getRepository(Stock::class)->findOneBy([
            'restaurant' => $restaurant,
            'product' => $product,
        ]);

        $form = $this->createForm(ProductType::class, $product);

        $form->add('quantity', IntegerType::class, [
            'label' => 'Quantité',
            'mapped' => false,
            'attr' => ['min' => 0],
            'data' => $stock->getQuantity(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stock->setQuantity($form->get('quantity')->getData());
            $entityManager->flush();

            return $this->redirectToRoute('app_stock_index', ['id' => $restaurant->getId()]);
        }

        return $this->render('stock/update.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $restaurant,
        ]);
    }
}
