<?php

namespace App\Repository;

use App\Entity\Package;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Package>
 *
 * @method Package|null find($id, $lockMode = null, $lockVersion = null)
 * @method Package|null findOneBy(array $criteria, array $orderBy = null)
 * @method Package[]    findAll()
 * @method Package[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Package::class);
    }

    public function getPackageNames(): array
    {
        return array_column(
            $this
                ->createQueryBuilder('p')
                ->select('p.name')
                ->distinct()
                ->getQuery()
                ->getScalarResult(),
            'name',
        );
    }

    public function getVersionCount(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getPackageCount(): int
    {
        return $this
            ->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.name)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
