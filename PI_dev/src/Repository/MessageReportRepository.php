<?php

namespace App\Repository;

use App\Entity\MessageReport;
use App\Entity\User;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageReport::class);
    }

    /**
     * Check if user has already reported this message
     */
    public function hasUserReported(Message $message, User $user): bool
    {
        return $this->createQueryBuilder('mr')
            ->select('COUNT(mr.id)')
            ->where('mr.message = :message')
            ->andWhere('mr.reporter = :user')
            ->setParameter('message', $message)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    /**
     * Find all pending reports
     */
    public function findPendingReports(): array
    {
        return $this->createQueryBuilder('mr')
            ->where('mr.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('mr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count reports for a specific message
     */
    public function countReportsForMessage(Message $message): int
    {
        return $this->createQueryBuilder('mr')
            ->select('COUNT(mr.id)')
            ->where('mr.message = :message')
            ->setParameter('message', $message)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
