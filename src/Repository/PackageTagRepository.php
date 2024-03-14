<?php

namespace App\Repository;

use App\Entity\PackageTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PackageTag>
 *
 * @method PackageTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method PackageTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method PackageTag[]    findAll()
 * @method PackageTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackageTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PackageTag::class);
    }

//    /**
//     * @return PackageTag[] Returns an array of PackageTag objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PackageTag
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
