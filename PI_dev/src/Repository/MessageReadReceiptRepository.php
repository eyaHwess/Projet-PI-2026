<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\MessageReadReceipt;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageReadReceiptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageReadReceipt::class);
    }

    /**
     * Check if a user has read a message
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
     * Get users who have read a message
     */
    public function getUsersWhoRead(Message $message): array
    {
        return $this->createQueryBuilder('r')
            ->select('u')
            ->join('r.user', 'u')
            ->where('r.message = :message')
            ->setParameter('message', $message)
            ->getQuery()
            ->getResult();
    }

    /**
     * Mark message as read for user
     */
    public function markAsRead(Message $message, User $user): void
    {
        if ($this->hasUserReadMessage($message, $user)) {
            return;
        }

        $receipt = new MessageReadReceipt();
        $receipt->setMessage($message);
        $receipt->setUser($user);
        $receipt->setReadAt(new \DateTime());

        $this->getEntityManager()->persist($receipt);
        $this->getEntityManager()->flush();
    }
    /**
     * Get unread message count for a user in a chatroom
     */
    public function getUnreadCountForUserInChatroom(User $user, $chatroom): int
    {
        $chatroomId = is_object($chatroom) ? $chatroom->getId() : $chatroom;
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        return $qb->select('COUNT(DISTINCT m.id)')
            ->from('App\Entity\Message', 'm')
            ->leftJoin('App\Entity\MessageReadReceipt', 'receipt', 'WITH', 'receipt.message = m AND receipt.user = :user')
            ->where('m.chatroom = :chatroomId')
            ->andWhere('m.author != :user')
            ->andWhere('receipt.id IS NULL')
            ->setParameter('user', $user)
            ->setParameter('chatroomId', $chatroomId)
            ->getQuery()
            ->getSingleScalarResult();
    }



}
