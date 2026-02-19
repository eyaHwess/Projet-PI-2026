<?php

namespace App\Repository;

use App\Entity\CoachingRequest;
use App\Entity\Session;
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
     * Demandes pour un coach avec recherche et filtres.
     * Filtres : search (nom, email, mot clé message), status, date_from, date_to, priority.
     * Statuts étendus : pending, accepted, declined, planned (acceptée + session non terminée), completed (session terminée).
     *
     * @param array{search?: string, status?: string, date_from?: string, date_to?: string, priority?: string} $filters
     * @return CoachingRequest[]
     */
    public function findForCoachWithFilters(User $coach, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('cr')
            ->leftJoin('cr.user', 'u')
            ->leftJoin('cr.session', 's')
            ->addSelect('u', 's')
            ->where('cr.coach = :coach')
            ->setParameter('coach', $coach);

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('u.firstName', ':search'),
                    $qb->expr()->like('u.lastName', ':search'),
                    $qb->expr()->like('u.email', ':search'),
                    $qb->expr()->like('cr.message', ':search')
                )
            )->setParameter('search', $term);
        }

        $status = $filters['status'] ?? null;
        if ($status !== null && $status !== '') {
            if ($status === 'planned') {
                $qb->andWhere('cr.status = :status')
                    ->andWhere('s.id IS NOT NULL')
                    ->andWhere('s.status != :session_completed')
                    ->setParameter('status', CoachingRequest::STATUS_ACCEPTED)
                    ->setParameter('session_completed', Session::STATUS_COMPLETED);
            } elseif ($status === 'completed') {
                $qb->andWhere('cr.status = :status')
                    ->andWhere('s.id IS NOT NULL')
                    ->andWhere('s.status = :session_completed')
                    ->setParameter('status', CoachingRequest::STATUS_ACCEPTED)
                    ->setParameter('session_completed', Session::STATUS_COMPLETED);
            } else {
                $qb->andWhere('cr.status = :status')
                    ->setParameter('status', $status);
            }
        }

        if (!empty($filters['date_from'])) {
            $from = \DateTimeImmutable::createFromFormat('Y-m-d', $filters['date_from']);
            if ($from) {
                $qb->andWhere('cr.createdAt >= :date_from')
                    ->setParameter('date_from', $from->setTime(0, 0, 0));
            }
        }
        if (!empty($filters['date_to'])) {
            $to = \DateTimeImmutable::createFromFormat('Y-m-d', $filters['date_to']);
            if ($to) {
                $qb->andWhere('cr.createdAt <= :date_to')
                    ->setParameter('date_to', $to->setTime(23, 59, 59));
            }
        }

        if (!empty($filters['priority'])) {
            $qb->andWhere('cr.priority = :priority')
                ->setParameter('priority', $filters['priority']);
        }

        $qb->orderBy('cr.priority', 'DESC')
            ->addOrderBy('cr.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
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
