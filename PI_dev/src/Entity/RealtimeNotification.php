<?php

namespace App\Entity;

use App\Repository\RealtimeNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RealtimeNotificationRepository::class)]
#[ORM\Table(name: 'realtime_notifications')]
#[ORM\Index(name: 'idx_rt_notification_recipient_read', columns: ['recipient_id', 'is_read', 'created_at'])]
class RealtimeNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $recipient = null;

    #[ORM\Column(length: 40)]
    private ?string $type = null;

    #[ORM\Column(type: 'text')]
    private ?string $message = null;

    #[ORM\ManyToOne(targetEntity: Post::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Post $relatedPost = null;

    #[ORM\ManyToOne(targetEntity: Comment::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Comment $relatedComment = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isRead = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(User $recipient): static
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getRelatedPost(): ?Post
    {
        return $this->relatedPost;
    }

    public function setRelatedPost(?Post $relatedPost): static
    {
        $this->relatedPost = $relatedPost;
        return $this;
    }

    public function getRelatedComment(): ?Comment
    {
        return $this->relatedComment;
    }

    public function setRelatedComment(?Comment $relatedComment): static
    {
        $this->relatedComment = $relatedComment;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}

