<?php

namespace App\Entity;

use App\Repository\MessageTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageTranslationRepository::class)]
#[ORM\Table(name: 'message_translation')]
#[ORM\Index(columns: ['message_id', 'target_language'], name: 'idx_message_lang')]
#[ORM\Index(columns: ['created_at'], name: 'idx_created_at')]
class MessageTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Message::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Message $message = null;

    #[ORM\Column(length: 10)]
    private ?string $sourceLanguage = null;

    #[ORM\Column(length: 10)]
    private ?string $targetLanguage = null;

    #[ORM\Column(type: 'text')]
    private ?string $translatedText = null;

    #[ORM\Column(length: 50)]
    private ?string $provider = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

    #[ORM\Column(options: ['default' => 1])]
    private int $usageCount = 1;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->lastUsedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getSourceLanguage(): ?string
    {
        return $this->sourceLanguage;
    }

    public function setSourceLanguage(string $sourceLanguage): static
    {
        $this->sourceLanguage = $sourceLanguage;
        return $this;
    }

    public function getTargetLanguage(): ?string
    {
        return $this->targetLanguage;
    }

    public function setTargetLanguage(string $targetLanguage): static
    {
        $this->targetLanguage = $targetLanguage;
        return $this;
    }

    public function getTranslatedText(): ?string
    {
        return $this->translatedText;
    }

    public function setTranslatedText(string $translatedText): static
    {
        $this->translatedText = $translatedText;
        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): static
    {
        $this->provider = $provider;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(?\DateTimeImmutable $lastUsedAt): static
    {
        $this->lastUsedAt = $lastUsedAt;
        return $this;
    }

    public function getUsageCount(): int
    {
        return $this->usageCount;
    }

    public function setUsageCount(int $usageCount): static
    {
        $this->usageCount = $usageCount;
        return $this;
    }

    public function incrementUsageCount(): static
    {
        $this->usageCount++;
        $this->lastUsedAt = new \DateTimeImmutable();
        return $this;
    }
}
