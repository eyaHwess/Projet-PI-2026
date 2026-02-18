<?php

namespace App\Repository;

use App\Entity\CoachingRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoachingRequest>
 */
class CoachingRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoachingRequest::class);
    }

    /**
     * @return CoachingRequest[]
     */
    public function findPendingForCoach(User $coach): array
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.coach = :coach')
            ->andWhere('cr.status = :status')
            ->setParameter('coach', $coach)
            ->setParameter('status', CoachingRequest::STATUS_PENDING)
            ->orderBy('cr.priority', 'DESC') // urgent avant standard
            ->addOrderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CoachingRequest[]
     */
    public function findAllForCoach(User $coach): array
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.coach = :coach')
            ->setParameter('coach', $coach)
            ->orderBy('cr.priority', 'DESC') // urgent avant standard
            ->addOrderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CoachingRequest[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.user = :user')
            ->setParameter('user', $user)
            ->orderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CoachingRequest[]
     */
    public function findAcceptedWithSessionForUser(User $user): array
    {
        return $this->createQueryBuilder('cr')
            ->leftJoin('cr.session', 's')
            ->where('cr.user = :user')
            ->andWhere('cr.status = :status')
            ->andWhere('s.id IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('status', CoachingRequest::STATUS_ACCEPTED)
            ->orderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Toutes les demandes (admin), ordonnées par date, avec pagination.
     *
     * @return CoachingRequest[]
     */
    public function findAllOrdered(int $limit = 10, int $offset = 0): array
    {
        return $this->createQueryBuilder('cr')
            ->orderBy('cr.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre total de demandes (admin).
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('cr')
            ->select('COUNT(cr.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nombre de demandes pour un coach (admin).
     */
    public function countByCoach(User $coach): int
    {
        return (int) $this->createQueryBuilder('cr')
            ->select('COUNT(cr.id)')
            ->where('cr.coach = :coach')
            ->setParameter('coach', $coach)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function hasPendingRequest(User $user, User $coach): bool
    {
        $count = $this->createQueryBuilder('cr')
            ->select('COUNT(cr.id)')
            ->where('cr.user = :user')
            ->andWhere('cr.coach = :coach')
            ->andWhere('cr.status = :status')
            ->setParameter('user', $user)
            ->setParameter('coach', $coach)
            ->setParameter('status', CoachingRequest::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Demandes acceptées sans session pour un coach (pour créer une session).
     *
     * @return CoachingRequest[]
     */
    public function findAcceptedWithoutSessionForCoach(User $coach): array
    {
        return $this->createQueryBuilder('cr')
            ->leftJoin('cr.session', 's')
            ->where('cr.coach = :coach')
            ->andWhere('cr.status = :status')
            ->andWhere('s.id IS NULL')
            ->setParameter('coach', $coach)
            ->setParameter('status', CoachingRequest::STATUS_ACCEPTED)
            ->orderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les demandes urgentes pour un coach
     */
    public function countUrgentForCoach(User $coach): int
    {
        return (int) $this->createQueryBuilder('cr')
            ->select('COUNT(cr.id)')
            ->where('cr.coach = :coach')
            ->andWhere('cr.priority = :priority')
            ->setParameter('coach', $coach)
            ->setParameter('priority', CoachingRequest::PRIORITY_URGENT)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les demandes par priorité pour un coach
     */
    public function countByPriorityForCoach(User $coach, string $priority): int
    {
        return (int) $this->createQueryBuilder('cr')
            ->select('COUNT(cr.id)')
            ->where('cr.coach = :coach')
            ->andWhere('cr.priority = :priority')
            ->setParameter('coach', $coach)
            ->setParameter('priority', $priority)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les demandes par statut pour un coach
     */
    public function countByStatusForCoach(User $coach, string $status): int
    {
        return (int) $this->createQueryBuilder('cr')
            ->select('COUNT(cr.id)')
            ->where('cr.coach = :coach')
            ->andWhere('cr.status = :status')
            ->setParameter('coach', $coach)
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
