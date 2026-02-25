<?php

namespace App\Repository;

use App\Entity\Suggestion;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SuggestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Suggestion::class);
    }

    /**
     * Dernière suggestion pour l'utilisateur (pour cache, ex. 24h).
     */
    public function findLatestForUser(User $user, int $maxAgeHours = 24): ?Suggestion
    {
        $since = (new \DateTimeImmutable())->modify("-{$maxAgeHours} hours");

        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :user')
            ->andWhere('s.createdAt >= :since')
            ->setParameter('user', $user)
            ->setParameter('since', $since)
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
