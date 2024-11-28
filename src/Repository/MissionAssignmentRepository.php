<?php

namespace App\Repository;

use App\Entity\MissionAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MissionAssignment>
 *
 * @method MissionAssignment|null find($id, $lockMode = null, $lockVersion = null)
 * @method MissionAssignment|null findOneBy(array $criteria, array $orderBy = null)
 * @method MissionAssignment[]    findAll()
 * @method MissionAssignment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MissionAssignment::class);
    }

    public function save(MissionAssignment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MissionAssignment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
