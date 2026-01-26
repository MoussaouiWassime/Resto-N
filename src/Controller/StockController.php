<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Restaurant;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Form\StockType;
use App\Repository\ProductRepository;
use App\Repository\RoleRepository;
use App\Repository\StockRepository;
use App\Security\Voter\RestaurantVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class StockController extends AbstractController
{
    #[Route('/restaurant/{id}/stock', name: 'app_stock_index')]
    #[IsGranted(RestaurantVoter::MANAGE, subject: 'restaurant')]
    public function index(
        ?Restaurant $restaurant,
        StockRepository $stockRepository): Response
    {
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant introuvable.');
        }

        return $this->render('stock/index.html.twig', [
            'restaurant' => $restaurant,
            'stocks' => $stockRepository->findBy(['restaurant' => $restaurant]),
        ]);
    }

    #[Route('/restaurant/{id}/stock/add', name: 'app_stock_add_list')]
    #[IsGranted(RestaurantVoter::MANAGE, subject: 'restaurant')]
    public function addList(
        ?Restaurant $restaurant,
        ProductRepository $productRepository,
        Request $request): Response
    {
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant introuvable.');
        }

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
    #[IsGranted(RestaurantVoter::MANAGE, subject: 'restaurant')]
    public function createStock(
        ?Restaurant $restaurant,
        #[MapEntity(mapping: ['productId' => 'id'])] Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant introuvable.');
        }

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
            $this->addFlash('success', 'Produit ajouté au stock avec succès.');

            return $this->redirectToRoute('app_stock_index', ['id' => $restaurant->getId()]);
        }

        return $this->render('stock/create.html.twig', [
            'form' => $form,
            'restaurant' => $restaurant,
            'product' => $product,
        ]);
    }

    #[Route('/restaurant/{id}/stock/new-product', name: 'app_product_new')]
    #[IsGranted(RestaurantVoter::MANAGE, subject: 'restaurant')]
    public function newProduct(
        ?Restaurant $restaurant,
        Request $request,
        EntityManagerInterface $em): Response
    {
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant introuvable.');
        }

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
            'form' => $form,
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/restaurant/{id}/stock/update/{productId}', name: 'app_stock_update')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(
        ?Restaurant $restaurant,
        RoleRepository $roleRepository,
        #[MapEntity(mapping: ['productId' => 'id'])] Product $product,
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant introuvable.');
        }

        $user = $this->getUser();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        if (null === $role || 'P' !== $role->getRole()) {
            return $this->redirectToRoute('app_restaurant', [], 307);
        }

        $stock = $entityManager->getRepository(Stock::class)->findOneBy([
            'restaurant' => $restaurant,
            'product' => $product,
        ]);

        $form = $this->createForm(ProductType::class, $product)
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantité',
                'mapped' => false,
                'attr' => ['min' => 0],
                'data' => $stock->getQuantity(),
            ])
            ->add('measureUnit', ChoiceType::class, [
                'label' => 'Unité',
                'mapped' => false,
                'choices' => [
                    'Pièce(s)' => 'pcs',
                    'Kilogramme (kg)' => 'kg',
                    'Gramme (g)' => 'g',
                    'Litre (L)' => 'L',
                    'Centilitre (cL)' => 'cL',
                    'Bouteille' => 'btl',
                    'Portion' => 'part',
                ],
                'data' => $stock->getMeasureUnit(),
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stock->setQuantity($form->get('quantity')->getData());
            $entityManager->flush();
            $this->addFlash('success', 'Quantité mise à jour.');

            return $this->redirectToRoute('app_stock_index', ['id' => $restaurant->getId()]);
        }

        return $this->render('stock/update.html.twig', [
            'form' => $form,
            'restaurant' => $restaurant,
            'stock' => $stock,
        ]);
    }

    #[Route('/restaurant/{id}/stock/delete', name: 'app_stock_delete')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(
        ?Restaurant $restaurant,
        RoleRepository $roleRepository,
        Stock $stock,
        Request $request,
        EntityManagerInterface $em): Response
    {
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant introuvable.');
        }

        $user = $this->getUser();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        if (null === $role || 'P' !== $role->getRole()) {
            return $this->redirectToRoute('app_restaurant', [], 307);
        }

        $form = $this->createFormBuilder()
            ->add('delete', SubmitType::class, ['label' => 'Supprimer', 'attr' => ['class' => 'btn btn-danger']])
            ->add('cancel', SubmitType::class, ['label' => 'Annuler', 'attr' => ['class' => 'btn btn-secondary']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $em->remove($stock);
                $em->flush();
                $this->addFlash('success', 'Produit retiré du stock.');
            }

            return $this->redirectToRoute('app_stock_index', ['id' => $restaurant->getId()]);
        }

        return $this->render('stock/delete.html.twig', [
            'stock' => $stock,
            'form' => $form,
        ]);
    }
}
