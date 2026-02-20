<?php

namespace App\Repository;

use App\Entity\MessageReadReceipt;
use App\Entity\User;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessageReadReceipt>
 */
class MessageReadReceiptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageReadReceipt::class);
    }

    /**
     * Check if a message has been read by a user
     */
    public function hasUserReadMessage(Message $message, User $user): bool
    {
        return $this->count(['message' => $message, 'user' => $user]) > 0;
    }

    /**
     * Get read count for a message (excluding author)
     */
    public function getReadCount(Message $message): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.message = :message')
            ->andWhere('r.user != :author')
            ->setParameter('message', $message)
            ->setParameter('author', $message->getAuthor())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get unread message count for a user in a chatroom
     */
    public function getUnreadCountForUserInChatroom(User $user, $chatroomId): int
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(DISTINCT m.id)')
            ->from(Message::class, 'm')
            ->leftJoin(MessageReadReceipt::class, 'r', 'WITH', 'r.message = m AND r.user = :user')
            ->where('m.chatroom = :chatroom')
            ->andWhere('m.author != :user')
            ->andWhere('r.id IS NULL')
            ->setParameter('user', $user)
            ->setParameter('chatroom', $chatroomId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
