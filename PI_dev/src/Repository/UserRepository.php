<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    private const ROLE_COACH = 'ROLE_COACH';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Indique si la base est PostgreSQL (colonnes json → LIKE impossible).
     */
    private function isPostgreSQL(): bool
    {
        $platform = $this->getEntityManager()->getConnection()->getDatabasePlatform();
        return $platform instanceof PostgreSQLPlatform;
    }

    /**
     * Trouve tous les coaches
     * @return User[]
     */
    public function findCoaches(): array
    {
        if ($this->isPostgreSQL()) {
            return $this->findCoachesPostgreSQL();
        }
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%' . self::ROLE_COACH . '%')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Requête coaches pour PostgreSQL (colonnes roles en json).
     * @return User[]
     */
    private function findCoachesPostgreSQL(?string $speciality = null): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT id FROM "user" WHERE (roles::text LIKE :role)';
        $params = ['role' => '%' . self::ROLE_COACH . '%'];
        if ($speciality !== null) {
            $sql .= ' AND speciality = :speciality';
            $params['speciality'] = $speciality;
        }
        $sql .= ' ORDER BY last_name ASC';
        $ids = $conn->executeQuery($sql, $params)->fetchFirstColumn();
        if ($ids === []) {
            return [];
        }
        return $this->createQueryBuilder('u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les coaches par spécialité
     * @return User[]
     */
    public function findCoachesBySpeciality(string $speciality): array
    {
        if ($this->isPostgreSQL()) {
            return $this->findCoachesPostgreSQL($speciality);
        }
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->andWhere('u.speciality = :speciality')
            ->setParameter('role', '%' . self::ROLE_COACH . '%')
            ->setParameter('speciality', $speciality)
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère toutes les spécialités des coaches
     * @return array
     */
    public function findAllCoachSpecialities(): array
    {
        if ($this->isPostgreSQL()) {
            $conn = $this->getEntityManager()->getConnection();
            $sql = 'SELECT DISTINCT speciality FROM "user" WHERE (roles::text LIKE :role) AND speciality IS NOT NULL ORDER BY speciality ASC';
            $result = $conn->executeQuery($sql, ['role' => '%' . self::ROLE_COACH . '%'])->fetchAllAssociative();
            return array_column($result, 'speciality');
        }
        $result = $this->createQueryBuilder('u')
            ->select('DISTINCT u.speciality')
            ->where('u.roles LIKE :role')
            ->andWhere('u.speciality IS NOT NULL')
            ->setParameter('role', '%' . self::ROLE_COACH . '%')
            ->orderBy('u.speciality', 'ASC')
            ->getQuery()
            ->getResult();
        return array_column($result, 'speciality');
    }

    /**
     * Recherche avancée de coaches avec filtres et tri
     * @return User[]
     */
    public function searchCoaches(array $criteria): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($this->isPostgreSQL()) {
            $qb->where('u.roles::text LIKE :role');
        } else {
            $qb->where('u.roles LIKE :role');
        }
        $qb->setParameter('role', '%' . self::ROLE_COACH . '%');

        // Recherche par nom, email ou spécialité
        if (!empty($criteria['query'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(u.firstName)', ':query'),
                    $qb->expr()->like('LOWER(u.lastName)', ':query'),
                    $qb->expr()->like('LOWER(u.email)', ':query'),
                    $qb->expr()->like('LOWER(u.speciality)', ':query'),
                    $qb->expr()->like('LOWER(u.bio)', ':query')
                )
            );
            $qb->setParameter('query', '%' . strtolower($criteria['query']) . '%');
        }

        // Filtre par spécialité
        if (!empty($criteria['speciality'])) {
            $qb->andWhere('u.speciality = :speciality')
               ->setParameter('speciality', $criteria['speciality']);
        }

        // Filtre par prix
        if (isset($criteria['minPrice'])) {
            $qb->andWhere('u.pricePerSession >= :minPrice')
               ->setParameter('minPrice', $criteria['minPrice']);
        }
        if (isset($criteria['maxPrice'])) {
            $qb->andWhere('u.pricePerSession <= :maxPrice')
               ->setParameter('maxPrice', $criteria['maxPrice']);
        }

        // Filtre par note
        if (isset($criteria['minRating'])) {
            $qb->andWhere('u.rating >= :minRating')
               ->setParameter('minRating', $criteria['minRating']);
        }

        // Filtre par disponibilité
        if (!empty($criteria['availability'])) {
            $qb->andWhere('u.availability = :availability')
               ->setParameter('availability', $criteria['availability']);
        }

        // Tri
        $sortBy = $criteria['sortBy'] ?? 'rating';
        $sortOrder = strtoupper($criteria['sortOrder'] ?? 'DESC');

        switch ($sortBy) {
            case 'price':
                $qb->orderBy('u.pricePerSession', $sortOrder);
                break;
            case 'rating':
                $qb->orderBy('u.rating', $sortOrder);
                break;
            case 'popularity':
                $qb->orderBy('u.totalSessions', $sortOrder);
                break;
            case 'availability':
                $qb->orderBy('u.availability', $sortOrder);
                break;
            default:
                $qb->orderBy('u.rating', 'DESC');
        }

        // Tri secondaire par nom
        $qb->addOrderBy('u.lastName', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère la plage de prix des coaches
     */
    public function getCoachPriceRange(): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('MIN(u.pricePerSession) as minPrice, MAX(u.pricePerSession) as maxPrice');

        if ($this->isPostgreSQL()) {
            $qb->where('u.roles::text LIKE :role');
        } else {
            $qb->where('u.roles LIKE :role');
        }
        $qb->setParameter('role', '%' . self::ROLE_COACH . '%')
           ->andWhere('u.pricePerSession IS NOT NULL');

        $result = $qb->getQuery()->getSingleResult();
        
        return [
            'min' => $result['minPrice'] ?? 0,
            'max' => $result['maxPrice'] ?? 100,
        ];
    }

    /**
     * Récupère toutes les disponibilités uniques
     */
    public function getAvailabilities(): array
    {
        if ($this->isPostgreSQL()) {
            $conn = $this->getEntityManager()->getConnection();
            $sql = 'SELECT DISTINCT availability FROM "user" WHERE (roles::text LIKE :role) AND availability IS NOT NULL ORDER BY availability ASC';
            $result = $conn->executeQuery($sql, ['role' => '%' . self::ROLE_COACH . '%'])->fetchAllAssociative();
            return array_column($result, 'availability');
        }

        $result = $this->createQueryBuilder('u')
            ->select('DISTINCT u.availability')
            ->where('u.roles LIKE :role')
            ->andWhere('u.availability IS NOT NULL')
            ->setParameter('role', '%' . self::ROLE_COACH . '%')
            ->orderBy('u.availability', 'ASC')
            ->getQuery()
            ->getResult();
        
        return array_column($result, 'availability');
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
