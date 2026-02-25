<?php

namespace App\Service\Moderation;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ModerationService
{
    private const API_URL = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze';
    
    private const ATTRIBUTES = [
        'TOXICITY',
        'INSULT',
        'PROFANITY',
        'THREAT',
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private LoggerInterface $moderationLogger,
        private string $apiKey,
        private float $toxicityThreshold = 0.7
    ) {
    }

    /**
     * Analyze content for toxicity and inappropriate language
     *
     * @param string $content The text content to analyze
     * @param User|null $user The user who submitted the content (optional)
     * @param string $entityType The type of entity (post, comment, etc.)
     * @return ModerationResult The moderation result
     */
    public function analyzeContent(string $content, ?User $user = null, string $entityType = 'unknown'): ModerationResult
    {
        // Skip empty content
        if (empty(trim($content))) {
            return new ModerationResult(
                isClean: true,
                scores: [],
                flaggedAttributes: [],
                message: null
            );
        }

        try {
            // Call Perspective API
            $response = $this->callPerspectiveApi($content);
            
            // Parse and evaluate results
            $result = $this->evaluateResponse($response);
            
            // Log violations (only if content is not clean)
            if (!$result->isClean() && $user) {
                $this->logViolation($user, $content, $result, $entityType);
            }
            
            return $result;
            
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Perspective API transport error', [
                'error' => $e->getMessage(),
                'content_preview' => substr($content, 0, 100),
            ]);
            
            // On API failure, allow content but log the error
            return new ModerationResult(
                isClean: true,
                scores: [],
                flaggedAttributes: [],
                message: 'Moderation service temporarily unavailable'
            );
            
        } catch (\Exception $e) {
            $this->logger->error('Perspective API error', [
                'error' => $e->getMessage(),
                'content_preview' => substr($content, 0, 100),
            ]);
            
            // On unexpected error, allow content but log
            return new ModerationResult(
                isClean: true,
                scores: [],
                flaggedAttributes: [],
                message: 'Moderation check failed'
            );
        }
    }

    /**
     * Analyze post content (title + content) and log as a single entity
     * This prevents duplicate log entries for title and content
     *
     * @param string $title Post title
     * @param string $content Post content
     * @param User|null $user The user who submitted the post
     * @param string $entityType The type of entity (post, post_edit)
     * @return ModerationResult Merged moderation result
     */
    public function analyzePost(string $title, string $content, ?User $user = null, string $entityType = 'post'): ModerationResult
    {
        // Analyze title and content separately (without logging)
        $titleResult = $this->analyzeContentWithoutLogging($title);
        $contentResult = $this->analyzeContentWithoutLogging($content);

        // Merge the results
        $mergedResult = ModerationResult::merge($titleResult, $contentResult);

        // Log violation only once if merged result is not clean
        if (!$mergedResult->isClean() && $user) {
            $this->logPostViolation($user, $title, $content, $mergedResult, $entityType);
        }

        return $mergedResult;
    }

    /**
     * Analyze content without logging (internal use)
     */
    private function analyzeContentWithoutLogging(string $content): ModerationResult
    {
        // Skip empty content
        if (empty(trim($content))) {
            return new ModerationResult(
                isClean: true,
                scores: [],
                flaggedAttributes: [],
                message: null
            );
        }

        try {
            // Call Perspective API
            $response = $this->callPerspectiveApi($content);
            
            // Parse and evaluate results (without logging)
            return $this->evaluateResponse($response);
            
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Perspective API transport error', [
                'error' => $e->getMessage(),
                'content_preview' => substr($content, 0, 100),
            ]);
            
            // On API failure, allow content but log the error
            return new ModerationResult(
                isClean: true,
                scores: [],
                flaggedAttributes: [],
                message: 'Moderation service temporarily unavailable'
            );
            
        } catch (\Exception $e) {
            $this->logger->error('Perspective API error', [
                'error' => $e->getMessage(),
                'content_preview' => substr($content, 0, 100),
            ]);
            
            // On unexpected error, allow content but log
            return new ModerationResult(
                isClean: true,
                scores: [],
                flaggedAttributes: [],
                message: 'Moderation check failed'
            );
        }
    }

    /**
     * Log a post violation (title + content combined)
     */
    private function logPostViolation(User $user, string $title, string $content, ModerationResult $result, string $entityType): void
    {
        // Combine title and content for preview
        $combinedPreview = sprintf(
            'Title: %s | Content: %s',
            substr($title, 0, 80),
            substr(strip_tags($content), 0, 120)
        );

        $this->moderationLogger->warning('Content moderation violation detected', [
            'user_id' => $user->getId(),
            'user_email' => $user->getEmail(),
            'entity_type' => $entityType,
            'content_preview' => $combinedPreview,
            'title_length' => strlen($title),
            'content_length' => strlen($content),
            'scores' => $result->getScores(),
            'flagged_attributes' => $result->getFlaggedAttributes(),
            'highest_score' => $result->getHighestScore(),
            'threshold' => $this->toxicityThreshold,
            'timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Log a moderation violation to the dedicated moderation log
     */
    private function logViolation(User $user, string $content, ModerationResult $result, string $entityType): void
    {
        $this->moderationLogger->warning('Content moderation violation detected', [
            'user_id' => $user->getId(),
            'user_email' => $user->getEmail(),
            'entity_type' => $entityType,
            'content_preview' => substr($content, 0, 200), // First 200 chars
            'content_length' => strlen($content),
            'scores' => $result->getScores(),
            'flagged_attributes' => $result->getFlaggedAttributes(),
            'highest_score' => $result->getHighestScore(),
            'threshold' => $this->toxicityThreshold,
            'timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Call Google Perspective API
     */
    private function callPerspectiveApi(string $content): array
    {
        $requestBody = [
            'comment' => [
                'text' => $content,
            ],
            'languages' => ['en', 'fr'], // Add languages you support
            'requestedAttributes' => $this->buildAttributesRequest(),
        ];

        $response = $this->httpClient->request('POST', self::API_URL, [
            'query' => [
                'key' => $this->apiKey,
            ],
            'json' => $requestBody,
            'timeout' => 10,
        ]);

        $statusCode = $response->getStatusCode();
        
        if ($statusCode !== 200) {
            $errorBody = $response->getContent(false); // Get content even on error
            $this->logger->error('Perspective API error response', [
                'status' => $statusCode,
                'body' => $errorBody,
                'request' => $requestBody,
            ]);
            throw new \RuntimeException('Perspective API returned non-200 status: ' . $statusCode);
        }

        return $response->toArray();
    }

    /**
     * Build the attributes request structure
     */
    private function buildAttributesRequest(): array
{
    $attributes = [];

    foreach (self::ATTRIBUTES as $attribute) {
        $attributes[$attribute] = (object) [];
    }

    return $attributes;
}

    /**
     * Evaluate API response and determine if content is clean
     */
    private function evaluateResponse(array $response): ModerationResult
    {
        $scores = [];
        $flaggedAttributes = [];

        // Extract scores from response
        if (isset($response['attributeScores'])) {
            foreach ($response['attributeScores'] as $attribute => $data) {
                $score = $data['summaryScore']['value'] ?? 0;
                $scores[$attribute] = round($score, 3);

                // Check if score exceeds threshold
                if ($score >= $this->toxicityThreshold) {
                    $flaggedAttributes[] = $attribute;
                }
            }
        }

        $isClean = empty($flaggedAttributes);
        
        $message = $isClean 
            ? null 
            : $this->buildRejectionMessage($flaggedAttributes, $scores);

        return new ModerationResult(
            isClean: $isClean,
            scores: $scores,
            flaggedAttributes: $flaggedAttributes,
            message: $message
        );
    }

    /**
     * Build a user-friendly rejection message
     */
    private function buildRejectionMessage(array $flaggedAttributes, array $scores): string
    {
        $attributeNames = [
            'TOXICITY' => 'toxic language',
            'INSULT' => 'insults',
            'PROFANITY' => 'profanity',
            'THREAT' => 'threats',
        ];

        $reasons = array_map(
            fn($attr) => $attributeNames[$attr] ?? strtolower($attr),
            $flaggedAttributes
        );

        $reasonText = count($reasons) === 1 
            ? $reasons[0]
            : implode(', ', array_slice($reasons, 0, -1)) . ' and ' . end($reasons);

        return sprintf(
            'Your content contains inappropriate language (%s) and cannot be published. Please revise your message and try again.',
            $reasonText
        );
    }

    /**
     * Get the current toxicity threshold
     */
    public function getThreshold(): float
    {
        return $this->toxicityThreshold;
    }

    /**
     * Set a custom threshold for this instance
     */
    public function setThreshold(float $threshold): self
    {
        if ($threshold < 0 || $threshold > 1) {
            throw new \InvalidArgumentException('Threshold must be between 0 and 1');
        }
        
        $this->toxicityThreshold = $threshold;
        return $this;
    }
}
