<?php
namespace App\Repository;

use App\Entity\SavedPost;
use App\Entity\User;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SavedPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedPost::class);
    }

    public function findByUserAndPost(User $user, Post $post): ?SavedPost
    {
        return $this->findOneBy([
            'user' => $user,
            'post' => $post
        ]);
    }

    public function hasUserSavedPost(User $user, Post $post): bool
    {
        return $this->findByUserAndPost($user, $post) !== null;
    }

    public function findSavedPostsByUser(User $user): array
    {
        return $this->createQueryBuilder('sp')
            ->where('sp.user = :user')
            ->setParameter('user', $user)
            ->orderBy('sp.savedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
