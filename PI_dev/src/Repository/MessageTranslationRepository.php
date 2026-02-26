<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\MessageTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessageTranslation>
 */
class MessageTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageTranslation::class);
    }

    /**
     * Trouve une traduction existante pour un message et une langue cible
     */
    public function findExistingTranslation(Message $message, string $targetLanguage): ?MessageTranslation
    {
        return $this->createQueryBuilder('mt')
            ->where('mt.message = :message')
            ->andWhere('mt.targetLanguage = :targetLanguage')
            ->setParameter('message', $message)
            ->setParameter('targetLanguage', $targetLanguage)
            ->orderBy('mt.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère toutes les traductions d'un message
     */
    public function findByMessage(Message $message): array
    {
        return $this->createQueryBuilder('mt')
            ->where('mt.message = :message')
            ->setParameter('message', $message)
            ->orderBy('mt.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de traductions pour un message
     */
    public function countByMessage(Message $message): int
    {
        return $this->createQueryBuilder('mt')
            ->select('COUNT(mt.id)')
            ->where('mt.message = :message')
            ->setParameter('message', $message)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les statistiques d'utilisation des traductions
     */
    public function getUsageStats(): array
    {
        return $this->createQueryBuilder('mt')
            ->select('mt.provider, mt.targetLanguage, COUNT(mt.id) as count, SUM(mt.usageCount) as totalUsage')
            ->groupBy('mt.provider, mt.targetLanguage')
            ->orderBy('totalUsage', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime les traductions anciennes (plus de X jours)
     */
    public function deleteOldTranslations(int $days = 30): int
    {
        $date = new \DateTimeImmutable("-{$days} days");
        
        return $this->createQueryBuilder('mt')
            ->delete()
            ->where('mt.lastUsedAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }

    /**
     * Récupère les traductions les plus utilisées
     */
    public function getMostUsedTranslations(int $limit = 10): array
    {
        return $this->createQueryBuilder('mt')
            ->orderBy('mt.usageCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
