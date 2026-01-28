<?php

namespace App\Service;

use App\Entity\Restaurant;
use App\Entity\Statistic;
use App\Repository\StatisticRepository;
use Doctrine\ORM\EntityManagerInterface;

class StatisticService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StatisticRepository $statisticRepository,
    ) {
    }

    public function updateStatistic(Restaurant $restaurant, string $type, \DateTime $date, float $delta): void
    {
        $statDate = (clone $date)->setTime(0, 0);

        $statistic = $this->statisticRepository->findOneBy([
            'restaurant' => $restaurant,
            'statisticType' => $type,
            'date' => $statDate,
        ]);

        if (!$statistic && $delta > 0) {
            $statistic = new Statistic();
            $statistic->setRestaurant($restaurant);
            $statistic->setStatisticType($type);
            $statistic->setDate($statDate);
            $statistic->setValue(0);
            $this->entityManager->persist($statistic);
        }

        if (!$statistic && $delta <= 0) {
            return;
        }

        $newValue = $statistic->getValue() + $delta;
        $statistic->setValue($newValue);

        if ($statistic->getValue() <= 0) {
            $this->entityManager->remove($statistic);
        }
    }
}
