<?php

namespace App\Repository;

use App\Entity\Mission;
use App\Entity\Power;
use App\Entity\SuperHero;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mission>
 */
class MissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mission::class);
    }

    /**
     * Trouve les missions disponibles pour un super-héros en fonction de ses pouvoirs
     */
    public function findAvailableMissionsForHero(SuperHero $hero): array
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.status = :status')
            ->andWhere('m.superHero IS NULL')
            ->setParameter('status', 'pending');

        // Si le héros a des pouvoirs, on vérifie que la mission requiert ces pouvoirs
        if ($hero->getPower()) {
            $qb->leftJoin('m.requiredPowers', 'p')
               ->andWhere('p.id = :powerId OR p.id IS NULL')
               ->setParameter('powerId', $hero->getPower()->getId());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les missions terminées, triées par score
     */
    public function findCompletedMissionsOrderedByScore(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.status = :status')
            ->andWhere('m.score IS NOT NULL')
            ->setParameter('status', 'completed')
            ->orderBy('m.score', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les missions annulées qui doivent être supprimées
     */
    public function findMissionsToDelete(): array
    {
        $now = new \DateTimeImmutable();
        $qb = $this->createQueryBuilder('m')
            ->where('m.status = :status')
            ->andWhere('m.cancelledAt IS NOT NULL')
            ->setParameter('status', 'cancelled');

        return $qb->getQuery()->getResult();
    }
}
