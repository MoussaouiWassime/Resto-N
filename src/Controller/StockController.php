<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Restaurant;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Form\StockType;
use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

    #[Route('/restaurant/{id}/stock/add', name: 'app_stock_add_list')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function addList(Restaurant $restaurant, ProductRepository $productRepository, Request $request): Response
    {
        $searchText = $request->query->get('q');

        if ($searchText) {
            $products = $productRepository->createQueryBuilder('p')
                ->where('p.productName LIKE :term')
                ->setParameter('term', '%'.$searchText.'%')
                ->getQuery()
                ->getResult();
        } else {
            $products = $productRepository->findAll();
        }

        return $this->render('stock/add_list.html.twig', [
            'restaurant' => $restaurant,
            'products' => $products,
            'searchText' => $searchText,
        ]);
    }

    #[Route('/restaurant/{id}/stock/create/{productId}', name: 'app_stock_create')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createStock(
        Restaurant $restaurant,
        #[MapEntity(mapping: ['productId' => 'id'])] Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $existingStock = $entityManager->getRepository(Stock::class)->findOneBy([
            'restaurant' => $restaurant,
            'product' => $product,
        ]);

        if ($existingStock) {
            return $this->redirectToRoute('app_stock_update', [
                'id' => $restaurant->getId(),
                'productId' => $product->getId(),
            ]);
        }

        $stock = new Stock();
        $stock->setRestaurant($restaurant);
        $stock->setProduct($product);

        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stock);
            $entityManager->flush();

            return $this->redirectToRoute('app_stock_index', ['id' => $restaurant->getId()]);
        }

        return $this->render('stock/create.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $restaurant,
            'product' => $product,
        ]);
    }

    #[Route('/restaurant/{id}/stock/new-product', name: 'app_product_new')]
    public function newProduct(Restaurant $restaurant, Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('app_stock_create', [
                'id' => $restaurant->getId(),
                'productId' => $product->getId(),
            ]);
        }

        return $this->render('stock/new_product.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/restaurant/{id}/stock/update/{productId}', name: 'app_stock_update')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(
        Restaurant $restaurant,
        #[MapEntity(mapping: ['productId' => 'id'])] Product $product,
        Request $request,
        EntityManagerInterface $entityManager): Response
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

    #[Route('/restaurant/{id}/stock/delete', name: 'app_stock_delete')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Stock $stock, Request $request, EntityManagerInterface $em): Response
    {
        $restaurantId = $stock->getRestaurant()->getId();

        $form = $this->createFormBuilder()
            ->add('delete', SubmitType::class, ['label' => 'Supprimer', 'attr' => ['class' => 'btn btn-danger']])
            ->add('cancel', SubmitType::class, ['label' => 'Annuler', 'attr' => ['class' => 'btn btn-secondary']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $em->remove($stock);
                $em->flush();
            }

            return $this->redirectToRoute('app_stock_index', ['id' => $restaurantId]);
        }

        return $this->render('stock/delete.html.twig', [
            'stock' => $stock,
            'form' => $form->createView(),
        ]);
    }
}
