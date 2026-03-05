<?php

namespace App\Repository;

use App\Entity\GoalParticipation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GoalParticipation>
 */
class GoalParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GoalParticipation::class);
    }

    /**
     * @return GoalParticipation[]
     */
    public function findApprovedByUser(User $user, int $maxResults = 100): array
    {
        return $this->createQueryBuilder('gp')
            ->join('gp.goal', 'g')
            ->where('gp.user = :user')
            ->andWhere('gp.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', GoalParticipation::STATUS_APPROVED)
            ->orderBy('gp.createdAt', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }
}
