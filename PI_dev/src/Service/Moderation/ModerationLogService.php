<?php

namespace App\Service\Moderation;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Parses Monolog JSON lines from var/log/moderation.log and allows marking entries as reviewed
 * by appending a review marker line to the same log file (no database storage).
 */
class ModerationLogService
{
    private const MESSAGE_VIOLATION = 'Content moderation violation detected';
    private const MESSAGE_REVIEWED = 'Moderation violation reviewed';

    public function __construct(
        private KernelInterface $kernel
    ) {
    }

    private function getLogPath(): string
    {
        return $this->kernel->getLogDir() . DIRECTORY_SEPARATOR . 'moderation.log';
    }

    /**
     * @return array<int,array{
     *   id:string,
     *   datetime:string|null,
     *   userId:int|null,
     *   userEmail:string|null,
     *   entityType:string|null,
     *   contentPreview:string|null,
     *   scores:array<string,float>,
     *   highestScore:float|null,
     *   threshold:float|null,
     *   flaggedAttributes:string[],
     *   reviewed:bool,
     *   reviewedAt:string|null,
     *   reviewedBy:int|null,
     * }>
     */
    public function getViolations(int $limit = 200): array
    {
        $path = $this->getLogPath();
        if (!is_file($path)) {
            return [];
        }

        $reviewedMap = [];
        $violations = [];

        $file = new \SplFileObject($path, 'r');
        $file->setFlags(\SplFileObject::DROP_NEW_LINE | \SplFileObject::SKIP_EMPTY);

        foreach ($file as $line) {
            if (!is_string($line) || $line === '') {
                continue;
            }

            $decoded = json_decode($line, true);
            if (!is_array($decoded)) {
                continue;
            }

            $message = $decoded['message'] ?? null;
            if (!is_string($message)) {
                continue;
            }

            if ($message === self::MESSAGE_REVIEWED) {
                $ctx = $decoded['context'] ?? [];
                if (is_array($ctx) && isset($ctx['entry_id']) && is_string($ctx['entry_id'])) {
                    $reviewedMap[$ctx['entry_id']] = [
                        'reviewedAt' => isset($ctx['reviewed_at']) && is_string($ctx['reviewed_at']) ? $ctx['reviewed_at'] : null,
                        'reviewedBy' => isset($ctx['reviewed_by']) ? (int) $ctx['reviewed_by'] : null,
                    ];
                }
                continue;
            }

            if ($message !== self::MESSAGE_VIOLATION) {
                continue;
            }

            $ctx = $decoded['context'] ?? [];
            if (!is_array($ctx)) {
                $ctx = [];
            }

            $entryId = $this->buildEntryId($decoded);
            $violations[] = [
                'id' => $entryId,
                'datetime' => isset($decoded['datetime']) && is_string($decoded['datetime']) ? $decoded['datetime'] : null,
                'userId' => isset($ctx['user_id']) ? (int) $ctx['user_id'] : null,
                'userEmail' => isset($ctx['user_email']) && is_string($ctx['user_email']) ? $ctx['user_email'] : null,
                'entityType' => isset($ctx['entity_type']) && is_string($ctx['entity_type']) ? $ctx['entity_type'] : null,
                'contentPreview' => isset($ctx['content_preview']) && is_string($ctx['content_preview']) ? $ctx['content_preview'] : null,
                'scores' => isset($ctx['scores']) && is_array($ctx['scores']) ? $ctx['scores'] : [],
                'highestScore' => isset($ctx['highest_score']) ? (float) $ctx['highest_score'] : null,
                'threshold' => isset($ctx['threshold']) ? (float) $ctx['threshold'] : null,
                'flaggedAttributes' => isset($ctx['flagged_attributes']) && is_array($ctx['flagged_attributes']) ? array_values($ctx['flagged_attributes']) : [],
                'reviewed' => false,
                'reviewedAt' => null,
                'reviewedBy' => null,
            ];
        }

        // Newest first
        $violations = array_reverse($violations);

        foreach ($violations as &$v) {
            $review = $reviewedMap[$v['id']] ?? null;
            if (is_array($review)) {
                $v['reviewed'] = true;
                $v['reviewedAt'] = $review['reviewedAt'] ?? null;
                $v['reviewedBy'] = $review['reviewedBy'] ?? null;
            }
        }
        unset($v);

        return array_slice($violations, 0, max(1, $limit));
    }

    public function markReviewed(string $entryId, int $adminUserId, ?string $adminEmail = null): void
    {
        $path = $this->getLogPath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $now = new \DateTimeImmutable();
        $payload = [
            'message' => self::MESSAGE_REVIEWED,
            'context' => [
                'entry_id' => $entryId,
                'reviewed_by' => $adminUserId,
                'reviewed_by_email' => $adminEmail,
                'reviewed_at' => $now->format('c'),
            ],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'moderation',
            'datetime' => $now->format('c'),
            'extra' => new \stdClass(),
        ];

        file_put_contents(
            $path,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Build a stable identifier for a violation entry based on fields that
     * exist in the Monolog JSON line.
     *
     * @param array<string,mixed> $decoded
     */
    private function buildEntryId(array $decoded): string
    {
        $ctx = $decoded['context'] ?? [];
        if (!is_array($ctx)) {
            $ctx = [];
        }

        $parts = [
            (string) ($decoded['datetime'] ?? ''),
            (string) ($ctx['user_id'] ?? ''),
            (string) ($ctx['entity_type'] ?? ''),
            (string) ($ctx['content_preview'] ?? ''),
        ];

        return sha1(implode('|', $parts));
    }
}

