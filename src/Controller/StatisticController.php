<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use App\Repository\StatisticRepository;
use App\Security\Voter\RestaurantVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class StatisticController extends AbstractController
{
    private const STAT_TYPES = [
        'order' => [
            'constant' => 'NB_COMMANDES',
            'label' => 'Commandes',
            'template' => 'statistic/orderStats.html.twig',
        ],
        'visit' => [
            'constant' => 'NB_VISITES',
            'label' => 'Visites',
            'template' => 'statistic/visitStats.html.twig',
        ],
        'income' => [
            'constant' => 'CA_JOURNALIER',
            'label' => "Chiffre d'Affaire",
            'template' => 'statistic/incomeStats.html.twig',
        ],
    ];

    #[Route('restaurant/{id}/statistic', name: 'app_statistic', requirements: ['id' => '\d+'])]
    #[IsGranted(RestaurantVoter::MANAGE, subject: 'restaurant')]
    public function index(
        int $id,
        StatisticRepository $statisticRepository,
        RestaurantRepository $restaurantRepository,
    ): Response {
        $restaurant = $restaurantRepository->findWithId($id);
        $statistics = $statisticRepository->findBy(
            ['restaurant' => $restaurant],
            ['date' => 'ASC']
        );

        return $this->render('statistic/index.html.twig', [
            'statistics' => $statistics,
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('restaurant/{id}/statistic/{type}', name: 'app_statistic_show', requirements: ['id' => '\d+'])]
    #[IsGranted(RestaurantVoter::MANAGE, subject: 'restaurant')]
    public function showStats(
        int $id,
        string $type,
        StatisticRepository $statisticRepository,
        RestaurantRepository $restaurantRepository,
    ): Response {
        $restaurant = $restaurantRepository->findWithId($id);

        $statType = self::STAT_TYPES[$type];

        $statistics = $statisticRepository->findBy(
            ['restaurant' => $restaurant, 'statisticType' => $statType['constant']],
            ['date' => 'ASC']
        );

        if (empty($statistics)) {
            $this->addFlash('danger', "Aucune donnée de Commandes n'a été trouvée.");

            return $this->redirectToRoute('app_statistic', [
                'id' => $restaurant->getId(),
                'restaurant' => $restaurant,
            ], 307);
        }

        return $this->render($statType['template'], [
            'statistics' => $statistics,
            'restaurant' => $restaurant,
        ]);
    }
}
