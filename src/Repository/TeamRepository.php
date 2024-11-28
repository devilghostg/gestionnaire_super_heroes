<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

//    /**
//     * @return Team[] Returns an array of Team objects
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

//    public function findOneBySomeField($value): ?Team
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findBySearch(?string $search = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t', 'h')
            ->leftJoin('t.superHeroes', 'h');

        if ($search) {
            $qb->andWhere('t.name LIKE :search OR t.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('t.name', 'ASC')
                 ->getQuery()
                 ->getResult();
    }

    public function findWithFilters(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t', 'h')
            ->leftJoin('t.superHeroes', 'h');

        if (isset($filters['hasMembers']) && $filters['hasMembers'] === true) {
            $qb->andWhere('h.id IS NOT NULL');
        }

        if (isset($filters['isEmpty']) && $filters['isEmpty'] === true) {
            $qb->andWhere('h.id IS NULL');
        }

        return $qb->orderBy('t.name', 'ASC')
                 ->getQuery()
                 ->getResult();
    }
}
