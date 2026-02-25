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
     * Fetch existing tags by normalized names.
     *
     * @param string[] $names
     *
     * @return array<string,Tag> Indexed by tag name (normalized).
     */
    public function findByNamesIndexed(array $names): array
    {
        $names = array_values(array_unique(array_filter(array_map(
            static fn($n) => strtolower(trim((string) $n)),
            $names
        ))));

        if ($names === []) {
            return [];
        }

        /** @var Tag[] $tags */
        $tags = $this->createQueryBuilder('t')
            ->where('t.name IN (:names)')
            ->setParameter('names', $names)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($tags as $tag) {
            $name = $tag->getName();
            if ($name !== null && $name !== '') {
                $indexed[$name] = $tag;
            }
        }

        return $indexed;
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
