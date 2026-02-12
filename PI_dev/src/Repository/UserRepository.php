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
