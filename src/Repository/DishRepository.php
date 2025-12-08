<?php

namespace App\Repository;

use App\Entity\Dish;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dish>
 */
class DishRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dish::class);
    }

    //    /**
    //     * @return Dish[] Returns an array of Dish objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Dish
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function search(string $text): array
    {
        if ('' == trim($text)) {
            $qb = $this->createQueryBuilder('d')
                ->select(['d.id', 'd.name', 'd.description', 'd.price', 'd.category', 'd.photo'])
                ->orderBy('d.name', 'ASC');
        } else {
            $qb = $this->createQueryBuilder('d')
                ->select(['d.id', 'd.name', 'd.description', 'd.price', 'd.category', 'd.photo'])
                ->where('d.name LIKE :text')
                ->setParameter('text', '%'.$text.'%')
                ->orderBy('d.name', 'ASC');
        }
        $query = $qb->getQuery();

        return $query->execute();

        // to get just one result:
        // $product = $query->setMaxResults(1)->getOneOrNullResult();
    }


}
