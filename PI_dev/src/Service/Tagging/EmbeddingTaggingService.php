<?php

namespace App\Service\Tagging;

use App\Entity\Post;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmbeddingTaggingService implements TaggingInterface
{
    /**
     * @param array<string,array{label:string,embedding:float[]}> $categoryEmbeddings
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private TagRepository $tagRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private array $categoryEmbeddings,
        private string $embeddingApiEndpoint,
        private ?string $embeddingApiKey = null,
        private int $maxTags = 2
    ) {
    }

    /**
     * Generate tags using semantic embeddings.
     *
     * @return Tag[]
     */
    public function generateTagsForPost(Post $post, ?int $maxTags = null): array
    {
        $limit = $maxTags ?? $this->maxTags;

        try {
            $text = $this->extractCleanText($post);

            if ($text === '') {
                $this->logger->warning('No text to extract semantic tags from', ['post_id' => $post->getId()]);
                return [];
            }

            $postEmbedding = $this->fetchEmbedding($text);
            if (empty($postEmbedding)) {
                $this->logger->warning('Embedding API returned an empty vector', ['post_id' => $post->getId()]);
                return [];
            }

            $scores = $this->scoreCategories($postEmbedding);
            if (empty($scores)) {
                return [];
            }

            arsort($scores);
            $topCategories = array_slice(array_keys($scores), 0, $limit, true);

            $tags = [];
            foreach ($topCategories as $key) {
                $config = $this->categoryEmbeddings[$key] ?? null;
                if (!$config) {
                    continue;
                }

                $label = strtolower(trim((string) $config['label']));
                // Enforce "single-word tags only" across all strategies.
                if ($label === '' || str_contains($label, ' ')) {
                    continue;
                }
                $tag = $this->tagRepository->findOrCreate($label);

                if (!$post->hasTag($tag)) {
                    $post->addTag($tag);
                    $tag->incrementUsageCount();
                    $tags[] = $tag;

                    $this->entityManager->persist($tag);
                }
            }

            if (!empty($tags)) {
                $this->entityManager->flush();

                $this->logger->info('Embedding-based tags generated for post', [
                    'post_id' => $post->getId(),
                    'tags' => array_map(static fn(Tag $t) => $t->getName(), $tags),
                ]);
            }

            return $tags;
        } catch (\Throwable $e) {
            $this->logger->error('Error generating embedding-based tags', [
                'post_id' => $post->getId(),
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Regenerate tags for an existing post.
     *
     * @return Tag[]
     */
    public function regenerateTagsForPost(Post $post, ?int $maxTags = null): array
    {
        foreach ($post->getTags() as $tag) {
            $tag->decrementUsageCount();
            $post->removeTag($tag);
        }

        $this->entityManager->flush();

        return $this->generateTagsForPost($post, $maxTags);
    }

    /**
     * Prepare a clean text representation of the post for embedding.
     */
    private function extractCleanText(Post $post): string
    {
        $title = $post->getTitle() ?? '';
        $content = $post->getContent() ?? '';

        $text = $title . ' ' . $content;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($text);
    }

    /**
     * Call the external embedding API to obtain a vector representation.
     *
     * The exact payload/response format will depend on your provider; adapt
     * this method accordingly.
     *
     * @return float[]
     */
    private function fetchEmbedding(string $text): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($this->embeddingApiKey) {
            $headers['Authorization'] = 'Bearer ' . $this->embeddingApiKey;
        }

        $response = $this->httpClient->request('POST', $this->embeddingApiEndpoint, [
            'headers' => $headers,
            'json' => [
                'input' => $text,
            ],
        ]);

        $data = $response->toArray(false);

        if (isset($data['embedding']) && is_array($data['embedding'])) {
            return array_map('floatval', $data['embedding']);
        }

        if (isset($data['data'][0]['embedding']) && is_array($data['data'][0]['embedding'])) {
            return array_map('floatval', $data['data'][0]['embedding']);
        }

        return [];
    }

    /**
     * Compute cosine similarity between two vectors.
     *
     * Cosine similarity measures the cosine of the angle between two vectors
     * \(a\) and \(b\). It is defined as:
     *
     * \[
     *   \cos(\theta) = \frac{a \cdot b}{\|a\| \cdot \|b\|}
     * \]
     *
     * where \(a \cdot b\) is the dot product and \(\|a\|\), \(\|b\|\) are the
     * Euclidean norms. Two vectors pointing in the same direction have a
     * similarity close to 1, orthogonal vectors are near 0, and opposite
     * directions approach -1. Using cosine similarity on embeddings lets us
     * compare meaning (direction) rather than raw magnitude.
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        $length = min(count($a), count($b));
        if ($length === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $length; $i++) {
            $va = (float) $a[$i];
            $vb = (float) $b[$i];

            $dot += $va * $vb;
            $normA += $va * $va;
            $normB += $vb * $vb;
        }

        if ($normA === 0.0 || $normB === 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Score each predefined category using cosine similarity.
     *
     * @param float[] $postEmbedding
     *
     * @return array<string,float>
     */
    private function scoreCategories(array $postEmbedding): array
    {
        $scores = [];

        foreach ($this->categoryEmbeddings as $key => $config) {
            if (empty($config['embedding']) || !is_array($config['embedding'])) {
                continue;
            }

            $scores[$key] = $this->cosineSimilarity($postEmbedding, $config['embedding']);
        }

        return $scores;
    }
}

