<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Trouve tous les coaches
     * @return User[]
     */
    public function findCoaches(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_COACH%')
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
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->andWhere('u.speciality = :speciality')
            ->setParameter('role', '%ROLE_COACH%')
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
        $result = $this->createQueryBuilder('u')
            ->select('DISTINCT u.speciality')
            ->where('u.roles LIKE :role')
            ->andWhere('u.speciality IS NOT NULL')
            ->setParameter('role', '%ROLE_COACH%')
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
