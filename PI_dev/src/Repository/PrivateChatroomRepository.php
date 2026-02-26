<?php

namespace App\Repository;

use App\Entity\PrivateChatroom;
use App\Entity\User;
use App\Entity\Goal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PrivateChatroomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrivateChatroom::class);
    }

    /**
     * Find all private chatrooms for a user in a specific goal
     */
    public function findByUserAndGoal(User $user, Goal $goal): array
    {
        return $this->createQueryBuilder('pc')
            ->where('pc.parentGoal = :goal')
            ->andWhere('pc.isActive = true')
            ->andWhere('pc.creator = :user OR :user MEMBER OF pc.members')
            ->setParameter('goal', $goal)
            ->setParameter('user', $user)
            ->orderBy('pc.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all private chatrooms created by a user
     */
    public function findByCreator(User $user): array
    {
        return $this->createQueryBuilder('pc')
            ->where('pc.creator = :user')
            ->andWhere('pc.isActive = true')
            ->setParameter('user', $user)
            ->orderBy('pc.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
