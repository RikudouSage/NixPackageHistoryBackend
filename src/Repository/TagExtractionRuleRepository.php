<?php

namespace App\Repository;

use App\Entity\TagExtractionRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TagExtractionRule>
 *
 * @method TagExtractionRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagExtractionRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagExtractionRule[]    findAll()
 * @method TagExtractionRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagExtractionRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagExtractionRule::class);
    }

//    /**
//     * @return TagExtractionRule[] Returns an array of TagExtractionRule objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TagExtractionRule
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
