<?php

namespace App\Service\Moderation;

class ModerationResult
{
    private bool $isClean;
    private array $scores;
    private array $flaggedAttributes;
    private ?string $message;

    public function __construct(
        bool $isClean,
        array $scores,
        array $flaggedAttributes = [],
        ?string $message = null
    ) {
        $this->isClean = $isClean;
        $this->scores = $scores;
        $this->flaggedAttributes = $flaggedAttributes;
        $this->message = $message;
    }

    public function isClean(): bool
    {
        return $this->isClean;
    }

    public function getScores(): array
    {
        return $this->scores;
    }

    public function getFlaggedAttributes(): array
    {
        return $this->flaggedAttributes;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getHighestScore(): float
    {
        return !empty($this->scores) ? max($this->scores) : 0.0;
    }

    public function toArray(): array
    {
        return [
            'isClean' => $this->isClean,
            'scores' => $this->scores,
            'flaggedAttributes' => $this->flaggedAttributes,
            'message' => $this->message,
            'highestScore' => $this->getHighestScore(),
        ];
    }

    /**
     * Merge multiple ModerationResult objects into one
     * Takes the highest score for each attribute and combines flagged attributes
     *
     * @param ModerationResult ...$results Variable number of ModerationResult objects
     * @return ModerationResult Merged result
     */
    public static function merge(ModerationResult ...$results): self
    {
        if (empty($results)) {
            return new self(
                isClean: true,
                scores: [],
                flaggedAttributes: [],
                message: null
            );
        }

        $mergedScores = [];
        $mergedFlaggedAttributes = [];
        $messages = [];

        // Merge scores (take highest value for each attribute)
        foreach ($results as $result) {
            foreach ($result->getScores() as $attribute => $score) {
                if (!isset($mergedScores[$attribute]) || $score > $mergedScores[$attribute]) {
                    $mergedScores[$attribute] = $score;
                }
            }

            // Collect flagged attributes
            $mergedFlaggedAttributes = array_merge(
                $mergedFlaggedAttributes,
                $result->getFlaggedAttributes()
            );

            // Collect messages
            if ($result->getMessage()) {
                $messages[] = $result->getMessage();
            }
        }

        // Remove duplicate flagged attributes
        $mergedFlaggedAttributes = array_unique($mergedFlaggedAttributes);
        $mergedFlaggedAttributes = array_values($mergedFlaggedAttributes); // Re-index

        // Determine if merged result is clean
        $isClean = empty($mergedFlaggedAttributes);

        // Use first message or create a combined one
        $message = $isClean ? null : ($messages[0] ?? 'Content contains inappropriate language');

        return new self(
            isClean: $isClean,
            scores: $mergedScores,
            flaggedAttributes: $mergedFlaggedAttributes,
            message: $message
        );
    }
}
