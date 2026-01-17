<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use App\Repository\RoleRepository;
use App\Repository\StatisticRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StatisticController extends AbstractController
{
    #[Route('restaurant/{id}/statistic', name: 'app_statistic', requirements: ['id' => '\d+'])]
    public function index(
        int $id,
        StatisticRepository $statisticRepository,
        RestaurantRepository $restaurantRepository,
        RoleRepository $roleRepository): Response
    {
        $restaurant = $restaurantRepository->findWithId($id);
        $user = $this->getUser();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        if (!$role || 'P' !== $role->getRole()) {
            $this->addFlash('danger', "Vous n'avez pas accès à ce contenu.");

            return $this->redirectToRoute('app_restaurant', [], 307);
        }

        $statistics = $statisticRepository->findBy(
            ['restaurant' => $restaurant],
            ['date' => 'ASC']
        );

        return $this->render('statistic/index.html.twig', [
            'statistics' => $statistics,
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('restaurant/{id}/statistic/order', name: 'app_statistic_order', requirements: ['id' => '\d+'])]
    public function orderStats(
        int $id,
        StatisticRepository $statisticRepository,
        RestaurantRepository $restaurantRepository,
        RoleRepository $roleRepository): Response
    {
        $restaurant = $restaurantRepository->findWithId($id);
        $user = $this->getUser();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        $statistics = $statisticRepository->findBy(
            ['restaurant' => $restaurant, 'statisticType' => 'NB_COMMANDES'],
            ['date' => 'ASC']
        );

        if (!$role || 'P' !== $role->getRole()) {
            $this->addFlash('danger', "Vous n'avez pas accès à ce contenu.");

            return $this->redirectToRoute('app_restaurant', [], 307);
        }

        if (empty($statistics)) {
            $this->addFlash('danger', "Aucune donnée de visites n'a été trouvée.");

            return $this->redirectToRoute('app_statistic', [
                'id' => $restaurant->getId(),
                'restaurant' => $restaurant,
            ], 307);
        }

        return $this->render('statistic/orderStats.html.twig', [
            'statistics' => $statistics,
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('restaurant/{id}/statistic/visit', name: 'app_statistic_visit', requirements: ['id' => '\d+'])]
    public function visitStats(
        int $id,
        StatisticRepository $statisticRepository,
        RestaurantRepository $restaurantRepository,
        RoleRepository $roleRepository): Response
    {
        $restaurant = $restaurantRepository->findWithId($id);
        $user = $this->getUser();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        $statistics = $statisticRepository->findBy(
            ['restaurant' => $restaurant, 'statisticType' => 'NB_VISITES'],
            ['date' => 'ASC']
        );

        if (null === $role || 'P' !== $role->getRole()) {
            $this->addFlash('danger', "Vous n'avez pas accès à ce contenu.");

            return $this->redirectToRoute('app_restaurant', [], 307);
        }

        if (empty($statistics)) {
            $this->addFlash('danger', "Aucune donnée de visites n'a été trouvée.");

            return $this->redirectToRoute('app_statistic', [
                'id' => $restaurant->getId(),
                'restaurant' => $restaurant,
            ], 307);
        }

        return $this->render('statistic/visitStats.html.twig', [
            'statistics' => $statistics,
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('restaurant/{id}/statistic/income', name: 'app_statistic_income', requirements: ['id' => '\d+'])]
    public function incomeStats(
        int $id,
        StatisticRepository $statisticRepository,
        RestaurantRepository $restaurantRepository,
        RoleRepository $roleRepository): Response
    {
        $restaurant = $restaurantRepository->findWithId($id);
        $user = $this->getUser();
        $role = $roleRepository->findOneBy(['user' => $user, 'restaurant' => $restaurant]);

        $statistics = $statisticRepository->findBy(
            ['restaurant' => $restaurant, 'statisticType' => 'CA_JOURNALIER'],
            ['date' => 'ASC']
        );

        if (null === $role || 'P' !== $role->getRole()) {
            $this->addFlash('danger', "Vous n'avez pas accès à ce contenu.");

            return $this->redirectToRoute('app_restaurant', [], 307);
        }

        if (empty($statistics)) {
            $this->addFlash('danger', "Aucune donnée de visites n'a été trouvée.");

            return $this->redirectToRoute('app_statistic', [
                'id' => $restaurant->getId(),
                'restaurant' => $restaurant,
            ], 307);
        }

        return $this->render('statistic/incomeStats.html.twig', [
            'statistics' => $statistics,
            'restaurant' => $restaurant,
        ]);
    }
}
