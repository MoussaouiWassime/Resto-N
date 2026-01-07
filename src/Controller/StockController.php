<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
