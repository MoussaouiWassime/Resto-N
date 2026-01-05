<?php

namespace App\Repository;

use App\Entity\Restaurant;
use App\Entity\RestaurantTable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RestaurantTable>
 */
class RestaurantTableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RestaurantTable::class);
    }

    public function findAvailableTable(Restaurant $restaurant, \DateTimeInterface $date, int $people): ?RestaurantTable
    {
        $start = (clone $date)->modify('-2 hours'); // clone pour pas modifier la date de base
        $end = (clone $date)->modify('+2 hours'); // pareil qu'en haut

        return $this->createQueryBuilder('t')
            ->where('t.restaurant = :restaurant')
            ->andWhere('t.capacity >= :people')
            // sous requête poru vérifier si la table est déjà prise
            ->andWhere('NOT EXISTS (
                SELECT r.id
                FROM App\Entity\Reservation r
                WHERE r.restaurantTable = t
                AND r.status = :status
                AND r.reservationDate > :start
                AND r.reservationDate < :end
            )')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('people', $people)
            ->setParameter('status', 'C')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('t.capacity', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return RestaurantTable[] Returns an array of RestaurantTable objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RestaurantTable
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
