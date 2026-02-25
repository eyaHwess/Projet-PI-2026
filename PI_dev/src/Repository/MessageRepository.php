<?php

namespace App\Repository;

use App\Entity\Chatroom;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Récupère les messages d'un chatroom triés par date
     */
    public function findByChatroomOrderedByDate(Chatroom $chatroom): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.chatroom = :chatroom')
            ->setParameter('chatroom', $chatroom)
            ->leftJoin('m.author', 'u')
            ->addSelect('u')
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les derniers messages d'un chatroom
     */
    public function findRecentMessages(Chatroom $chatroom, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.chatroom = :chatroom')
            ->setParameter('chatroom', $chatroom)
            ->leftJoin('m.author', 'u')
            ->addSelect('u')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Message[] Returns an array of Message objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Message
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
