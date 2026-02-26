<?php

namespace App\Repository;

use App\Entity\MessageReaction;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessageReaction>
 */
class MessageReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageReaction::class);
    }

    /**
     * Get reaction counts for a message
     */
    public function getReactionCounts(Message $message): array
    {
        $qb = $this->createQueryBuilder('mr')
            ->select('mr.reactionType', 'COUNT(mr.id) as count')
            ->where('mr.message = :message')
            ->setParameter('message', $message)
            ->groupBy('mr.reactionType');

        $results = $qb->getQuery()->getResult();

        $counts = [];
        foreach ($results as $result) {
            $counts[$result['reactionType']] = (int) $result['count'];
        }

        return $counts;
    }

    /**
     * Get user's reactions for a message
     */
    public function getUserReactions(Message $message, User $user): array
    {
        $reactions = $this->createQueryBuilder('mr')
            ->select('mr.reactionType')
            ->where('mr.message = :message')
            ->andWhere('mr.user = :user')
            ->setParameter('message', $message)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return array_column($reactions, 'reactionType');
    }

    /**
     * Find existing reaction
     */
    public function findUserReaction(Message $message, User $user, string $reactionType): ?MessageReaction
    {
        return $this->findOneBy([
            'message' => $message,
            'user' => $user,
            'reactionType' => $reactionType
        ]);
    }

    /**
     * Get users who reacted with a specific type
     */
    public function getUsersByReactionType(Message $message, string $reactionType): array
    {
        return $this->createQueryBuilder('mr')
            ->select('u')
            ->join('mr.user', 'u')
            ->where('mr.message = :message')
            ->andWhere('mr.reactionType = :type')
            ->setParameter('message', $message)
            ->setParameter('type', $reactionType)
            ->getQuery()
            ->getResult();
    }
}
