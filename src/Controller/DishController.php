<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Repository\DishRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class DishController extends AbstractController
{
    #[Route('/dish', name: 'app_dish')]
    public function index(DishRepository $dishes, #[MapQueryParameter] string $search = ''): Response
    {
        return $this->render('dish/index.html.twig', [
            'dishes' => $dishes->search($search),
            'search' => $search,
        ]);
    }

    #[Route('/dish/{id}', requirements: ['id' => '\d+'])]
    public function show(?Dish $dish): Response
    {
        if (null == $dish) {
            throw $this->createNotFoundException('Plat introuvable.');
        }

        return $this->render('dish/show.html.twig', [
            'dish' => $dish,
        ]);
    }
}
