<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Find or create a tag by name
     */
    public function findOrCreate(string $name): Tag
    {
        $normalizedName = strtolower(trim($name));
        
        $tag = $this->findOneBy(['name' => $normalizedName]);
        
        if (!$tag) {
            $tag = new Tag();
            $tag->setName($normalizedName);
        }
        
        return $tag;
    }

    /**
     * Get most used tags
     */
    public function findMostUsed(int $limit = 20): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.usageCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search tags by name
     */
    public function searchByName(string $query, int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.name LIKE :query')
            ->setParameter('query', '%' . strtolower($query) . '%')
            ->orderBy('t.usageCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all tags with usage count greater than 0, ordered by usage count.
     * Used for tag filter dropdowns.
     */
    public function findAvailableTags(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.usageCount > 0')
            ->orderBy('t.usageCount', 'DESC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find a tag by slug.
     */
    public function findBySlug(string $slug): ?Tag
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
