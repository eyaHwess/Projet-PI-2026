<?php

namespace App\Repository;

use App\Entity\FcmToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FcmToken>
 */
class FcmTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FcmToken::class);
    }

    /**
     * Get all tokens for a user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('ft')
            ->where('ft.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ft.lastUsedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find token by string value
     */
    public function findByToken(string $token): ?FcmToken
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * Get all tokens for multiple users
     */
    public function findByUsers(array $users): array
    {
        if (empty($users)) {
            return [];
        }

        return $this->createQueryBuilder('ft')
            ->where('ft.user IN (:users)')
            ->setParameter('users', $users)
            ->getQuery()
            ->getResult();
    }

    /**
     * Remove expired tokens (not used in last 90 days)
     */
    public function removeExpiredTokens(): int
    {
        $expiryDate = new \DateTimeImmutable('-90 days');

        return $this->createQueryBuilder('ft')
            ->delete()
            ->where('ft.lastUsedAt < :expiryDate')
            ->setParameter('expiryDate', $expiryDate)
            ->getQuery()
            ->execute();
    }

    /**
     * Remove invalid tokens
     */
    public function removeInvalidTokens(array $invalidTokens): int
    {
        if (empty($invalidTokens)) {
            return 0;
        }

        return $this->createQueryBuilder('ft')
            ->delete()
            ->where('ft.token IN (:tokens)')
            ->setParameter('tokens', $invalidTokens)
            ->getQuery()
            ->execute();
    }
}
