<?php

namespace App\Service\Tagging;

use App\Entity\Post;
use App\Entity\Tag;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TfIdfTaggingService implements TaggingInterface
{
    // Stop words for English and French
    private const STOP_WORDS = [
        // English
        'the', 'be', 'to', 'of', 'and', 'a', 'in', 'that', 'have', 'i',
        'it', 'for', 'not', 'on', 'with', 'he', 'as', 'you', 'do', 'at',
        'this', 'but', 'his', 'by', 'from', 'they', 'we', 'say', 'her', 'she',
        'or', 'an', 'will', 'my', 'one', 'all', 'would', 'there', 'their', 'what',
        'so', 'up', 'out', 'if', 'about', 'who', 'get', 'which', 'go', 'me',
        'when', 'make', 'can', 'like', 'time', 'no', 'just', 'him', 'know', 'take',
        'people', 'into', 'year', 'your', 'good', 'some', 'could', 'them', 'see', 'other',
        'than', 'then', 'now', 'look', 'only', 'come', 'its', 'over', 'think', 'also',
        'back', 'after', 'use', 'two', 'how', 'our', 'work', 'first', 'well', 'way',
        'even', 'new', 'want', 'because', 'any', 'these', 'give', 'day', 'most', 'us',
        'is', 'was', 'are', 'been', 'has', 'had', 'were', 'said', 'did', 'having',
        'may', 'should', 'am', 'being', 'does', 'did', 'doing', 'done',
        // French
        'le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou',
        'mais', 'donc', 'or', 'ni', 'car', 'ce', 'cet', 'cette', 'ces',
        'mon', 'ton', 'son', 'ma', 'ta', 'sa', 'mes', 'tes', 'ses',
        'notre', 'votre', 'leur', 'nos', 'vos', 'leurs',
        'je', 'tu', 'il', 'elle', 'nous', 'vous', 'ils', 'elles',
        'me', 'te', 'se', 'moi', 'toi', 'lui', 'eux',
        'qui', 'que', 'quoi', 'dont', 'où', 'quand', 'comment', 'pourquoi',
        'dans', 'sur', 'sous', 'avec', 'sans', 'pour', 'par', 'entre', 'vers', 'chez',
        'est', 'sont', 'était', 'étaient', 'été', 'être', 'avoir', 'avait', 'avaient', 'eu',
        'fait', 'faire', 'faisant', 'dit', 'dire', 'disant',
        'tout', 'tous', 'toute', 'toutes', 'autre', 'autres', 'même', 'mêmes',
        'plus', 'moins', 'très', 'trop', 'peu', 'beaucoup', 'assez',
        'si', 'oui', 'non', 'ne', 'pas', 'jamais', 'toujours', 'encore', 'déjà',
    ];

    private const MIN_WORD_LENGTH = 3;
    private const MAX_WORD_LENGTH = 30;

    /**
     * Ignore terms (unigrams/bigrams) that occur only once in the document.
     */
    private const MIN_TERM_OCCURRENCE = 2;

    /**
     * Minimum number of occurrences required for a bigram (two-word phrase).
     */
    private const MIN_BIGRAM_OCCURRENCE = 2;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PostRepository $postRepository,
        private TagRepository $tagRepository,
        private LoggerInterface $logger,
        private int $maxTags = 2
    ) {
    }

    /**
     * Generate and assign tags to a post using an optimized TF-IDF approach.
     *
     * @return Tag[]
     */
    public function generateTagsForPost(Post $post, ?int $maxTags = null): array
    {
        $limit = $maxTags ?? $this->maxTags;

        try {
            $title = $post->getTitle() ?? '';
            $content = $post->getContent() ?? '';

            if ($title === '' && $content === '') {
                $this->logger->warning('No text to extract tags from', ['post_id' => $post->getId()]);
                return [];
            }

            $titleTokens = $this->tokenize($title);
            $contentTokens = $this->tokenize(strip_tags($content));

            if (empty($titleTokens) && empty($contentTokens)) {
                $this->logger->warning('No valid tokens found', ['post_id' => $post->getId()]);
                return [];
            }

            $tfIdfScores = $this->calculateTfIdfScores($titleTokens, $contentTokens);
            if (empty($tfIdfScores)) {
                return [];
            }

            $keywords = $this->extractTopKeywords($tfIdfScores, $limit);

            // Ensure we don't generate duplicate tag labels (e.g. from overlapping n-grams)
            $keywords = array_values(array_unique($keywords));

            $tags = [];
            foreach ($keywords as $keyword) {
                $tag = $this->tagRepository->findOrCreate($keyword);

                if (!$post->hasTag($tag)) {
                    $post->addTag($tag);
                    $tag->incrementUsageCount();
                    $tags[] = $tag;

                    $this->entityManager->persist($tag);
                }
            }

            $this->entityManager->flush();

            $this->logger->info('TF-IDF tags generated for post', [
                'post_id' => $post->getId(),
                'tags' => array_map(static fn(Tag $t) => $t->getName(), $tags),
            ]);

            return $tags;
        } catch (\Throwable $e) {
            $this->logger->error('Error generating TF-IDF tags', [
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
     * Tokenize and normalize text to a list of lowercase words, filtering stop words.
     */
    private function tokenize(string $text): array
    {
        $text = mb_strtolower($text, 'UTF-8');

        $text = preg_replace('/[^\p{L}\s]/u', ' ', $text);

        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $tokens = [];
        foreach ($words as $word) {
            $word = trim($word);
            $length = mb_strlen($word, 'UTF-8');

            if (
                $length < self::MIN_WORD_LENGTH ||
                $length > self::MAX_WORD_LENGTH ||
                in_array($word, self::STOP_WORDS, true)
            ) {
                continue;
            }

            // Ignore random-looking strings with no vowels to avoid meaningless tags
            if (!preg_match('/[aeiouyàâäéèêëîïôöùûüÿœæ]/iu', $word)) {
                continue;
            }

            $tokens[] = $word;
        }

        return $tokens;
    }

    /**
     * Build weighted term frequencies including unigrams and bigrams.
     *
     * - Title tokens are given a numeric weight instead of duplicating the title string.
     * - Unigrams (single words) and bigrams (two-word phrases) must appear at least
     *   MIN_TERM_OCCURRENCE times (raw count) to be considered.
     * - Bigrams are only formed from distinct adjacent words; sequences like "word word"
     *   are ignored to avoid meaningless duplicates.
     */
    private function getTermFrequencyWithBigrams(array $titleTokens, array $contentTokens, float $titleWeight = 2.0): array
    {
        if (empty($titleTokens) && empty($contentTokens)) {
            return [];
        }

        $termRawCounts = [];
        $termWeightedCounts = [];

        // Unigram counts (raw + weighted) for title
        foreach ($titleTokens as $token) {
            $termRawCounts[$token] = ($termRawCounts[$token] ?? 0) + 1;
            $termWeightedCounts[$token] = ($termWeightedCounts[$token] ?? 0.0) + $titleWeight;
        }

        // Unigram counts (raw + weighted) for content
        foreach ($contentTokens as $token) {
            $termRawCounts[$token] = ($termRawCounts[$token] ?? 0) + 1;
            $termWeightedCounts[$token] = ($termWeightedCounts[$token] ?? 0.0) + 1.0;
        }

        // Apply minimum occurrence filter for unigrams
        foreach ($termRawCounts as $term => $rawCount) {
            if ($rawCount < self::MIN_TERM_OCCURRENCE) {
                unset($termRawCounts[$term], $termWeightedCounts[$term]);
            }
        }

        // Bigram counts (raw + weighted)
        $bigramRawCounts = [];
        $bigramWeightedCounts = [];

        $buildBigrams = static function (array $tokens, float $weight) use (&$bigramRawCounts, &$bigramWeightedCounts): void {
            $tokenCount = count($tokens);

            for ($i = 0; $i < $tokenCount - 1; $i++) {
                $first = $tokens[$i];
                $second = $tokens[$i + 1];

                // Skip bigrams where both words are identical (e.g. "word word")
                if ($first === $second) {
                    continue;
                }

                $bigram = $first . ' ' . $second;

                $bigramRawCounts[$bigram] = ($bigramRawCounts[$bigram] ?? 0) + 1;
                $bigramWeightedCounts[$bigram] = ($bigramWeightedCounts[$bigram] ?? 0.0) + $weight;
            }
        };

        $buildBigrams($titleTokens, $titleWeight);
        $buildBigrams($contentTokens, 1.0);

        foreach ($bigramRawCounts as $bigram => $rawCount) {
            if ($rawCount < self::MIN_BIGRAM_OCCURRENCE) {
                unset($bigramRawCounts[$bigram], $bigramWeightedCounts[$bigram]);
                continue;
            }

            $termRawCounts[$bigram] = $rawCount;
            $termWeightedCounts[$bigram] = ($termWeightedCounts[$bigram] ?? 0.0) + $bigramWeightedCounts[$bigram];
        }

        if (empty($termWeightedCounts)) {
            return [];
        }

        $totalWeighted = array_sum($termWeightedCounts);
        if ($totalWeighted <= 0.0) {
            return [];
        }

        $tf = [];
        foreach ($termWeightedCounts as $term => $weightedCount) {
            $tf[$term] = $weightedCount / $totalWeighted;
        }

        return $tf;
    }

    /**
     * Build an IDF index for all candidate terms in a single pass over the database.
     *
     * This removes the previous per-term COUNT query and avoids LIKE queries per term
     * by loading published posts once per tagging operation and computing
     * document frequencies in PHP.
     *
     * @param string[] $terms
     *
     * @return array<string,float> [term => idf]
     */
    private function buildIdfIndex(array $terms): array
    {
        $terms = array_values(array_unique(array_filter($terms)));
        if (empty($terms)) {
            return [];
        }

        $qb = $this->postRepository->createQueryBuilder('p')
            ->select('p.id, p.title, p.content')
            ->where('p.status = :status')
            ->setParameter('status', 'published');

        $rows = $qb->getQuery()->getArrayResult();
        $totalDocs = count($rows);

        if ($totalDocs === 0) {
            return [];
        }

        $docFreq = array_fill_keys($terms, 0);

        foreach ($rows as $row) {
            $title = $row['title'] ?? '';
            $content = $row['content'] ?? '';

            $text = strip_tags($title . ' ' . $content);
            $text = mb_strtolower($text, 'UTF-8');

            foreach ($terms as $term) {
                if ($docFreq[$term] === $totalDocs) {
                    continue;
                }

                if (str_contains($text, $term)) {
                    $docFreq[$term]++;
                }
            }
        }

        $idfIndex = [];
        foreach ($docFreq as $term => $docsWithTerm) {
            $docsWithTerm = max(1, $docsWithTerm);
            $idfIndex[$term] = log($totalDocs / $docsWithTerm);
        }

        return $idfIndex;
    }

    /**
     * Calculate TF-IDF scores for all terms (unigrams + bigrams).
     *
     * @return array<string,float>
     */
    private function calculateTfIdfScores(array $titleTokens, array $contentTokens): array
    {
        $tf = $this->getTermFrequencyWithBigrams($titleTokens, $contentTokens);
        if (empty($tf)) {
            return [];
        }

        $idfIndex = $this->buildIdfIndex(array_keys($tf));

        $tfIdf = [];
        foreach ($tf as $term => $frequency) {
            $idf = $idfIndex[$term] ?? 0.0;
            $tfIdf[$term] = $frequency * $idf;
        }

        return $tfIdf;
    }

    /**
     * Extract top N keywords based on TF-IDF scores.
     *
     * @param array<string,float> $tfIdfScores
     *
     * @return string[]
     */
    private function extractTopKeywords(array $tfIdfScores, int $limit): array
    {
        arsort($tfIdfScores);

        $topKeywords = array_slice(array_keys($tfIdfScores), 0, $limit);

        return $topKeywords;
    }
}

