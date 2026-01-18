<?php

namespace App\Repository;

use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Restaurant>
 */
class RestaurantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Restaurant::class);
    }

    public function search(string $text): array
    {
        $queryBuilder = $this->createQueryBuilder('c');
        if ('' != $text) {
            $queryBuilder->where('c.name LIKE :text')
                ->setParameter('text', "%{$text}%");
        }

        return $queryBuilder->orderBy('c.name', 'ASC')
            ->leftJoin('c.categories', 'cat')
            ->addSelect('cat')
            ->getQuery()
            ->getResult();
    }

    public function findWithId(int $id): ?Restaurant
    {
        return $this->createQueryBuilder('r')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function searchByCriteria(array $filters): array
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->leftJoin('r.categories', 'c')
            ->addSelect('c');

        if (!empty($filters['city'])) {
            $queryBuilder->andWhere('LOWER(r.city) LIKE :city')
                ->setParameter('city', '%'.strtolower($filters['city']).'%');
        }

        if (!empty($filters['category'])) {
            $queryBuilder->andWhere('LOWER(c.name) LIKE :category')
                ->setParameter('category', '%'.strtolower($filters['category']).'%');
        }

        if (!empty($filters['name'])) {
            $queryBuilder->andWhere('LOWER(r.name) LIKE :name')
                ->setParameter('name', '%'.strtolower($filters['name']).'%');
        }

        return $queryBuilder->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Restaurant[] Returns an array of Restaurant objects
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

    //    public function findOneBySomeField($value): ?Restaurant
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
