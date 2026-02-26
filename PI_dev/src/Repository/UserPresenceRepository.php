<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserPresence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserPresenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPresence::class);
    }

    /**
     * Get or create presence for user
     */
    public function getOrCreatePresence(User $user): UserPresence
    {
        $presence = $this->findOneBy(['user' => $user]);

        if (!$presence) {
            $presence = new UserPresence();
            $presence->setUser($user);
            $presence->setStatus('online');
            $presence->setLastSeenAt(new \DateTime());
            $presence->setLastActivityAt(new \DateTime());

            $this->getEntityManager()->persist($presence);
            $this->getEntityManager()->flush();
        }

        return $presence;
    }

    /**
     * Update user activity
     */
    public function updateActivity(User $user): void
    {
        $presence = $this->getOrCreatePresence($user);
        $presence->updateActivity();
        $this->getEntityManager()->flush();
    }

    /**
     * Set user typing status
     */
    public function setTyping(User $user, int $chatroomId, bool $isTyping): void
    {
        $presence = $this->getOrCreatePresence($user);
        $presence->setIsTyping($isTyping);
        $presence->setTypingInChatroomId($isTyping ? $chatroomId : null);
        $presence->setTypingStartedAt($isTyping ? new \DateTime() : null);
        
        if ($isTyping) {
            $presence->updateActivity();
        }

        $this->getEntityManager()->flush();
    }

    /**
     * Get users typing in chatroom
     */
    public function getUsersTypingInChatroom(int $chatroomId): array
    {
        $fiveSecondsAgo = new \DateTime('-5 seconds');

        return $this->createQueryBuilder('p')
            ->where('p.isTyping = :isTyping')
            ->andWhere('p.typingInChatroomId = :chatroomId')
            ->andWhere('p.typingStartedAt > :fiveSecondsAgo')
            ->setParameter('isTyping', true)
            ->setParameter('chatroomId', $chatroomId)
            ->setParameter('fiveSecondsAgo', $fiveSecondsAgo)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get online users count in chatroom
     */
    public function getOnlineUsersInChatroom(int $chatroomId): int
    {
        // This would require a join with chatroom participants
        // For now, return count of online users
        $fiveMinutesAgo = new \DateTime('-5 minutes');

        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.lastActivityAt > :fiveMinutesAgo')
            ->setParameter('fiveMinutesAgo', $fiveMinutesAgo)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Clean up old typing indicators (older than 10 seconds)
     */
    public function cleanupOldTypingIndicators(): void
    {
        $tenSecondsAgo = new \DateTime('-10 seconds');

        $this->createQueryBuilder('p')
            ->update()
            ->set('p.isTyping', ':false')
            ->set('p.typingInChatroomId', ':null')
            ->set('p.typingStartedAt', ':null')
            ->where('p.isTyping = :true')
            ->andWhere('p.typingStartedAt < :tenSecondsAgo')
            ->setParameter('false', false)
            ->setParameter('null', null)
            ->setParameter('true', true)
            ->setParameter('tenSecondsAgo', $tenSecondsAgo)
            ->getQuery()
            ->execute();
    }
}
