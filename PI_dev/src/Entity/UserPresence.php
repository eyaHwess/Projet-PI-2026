<?php

namespace App\Entity;

use App\Repository\UserPresenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPresenceRepository::class)]
#[ORM\Table(name: 'user_presence')]
class UserPresence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'offline'; // online, away, offline

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $lastSeenAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastActivityAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isTyping = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $typingInChatroomId = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $typingStartedAt = null;

    public function __construct()
    {
        $this->lastSeenAt = new \DateTime();
        $this->lastActivityAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getLastSeenAt(): ?\DateTimeInterface
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(\DateTimeInterface $lastSeenAt): static
    {
        $this->lastSeenAt = $lastSeenAt;
        return $this;
    }

    public function getLastActivityAt(): ?\DateTimeInterface
    {
        return $this->lastActivityAt;
    }

    public function setLastActivityAt(?\DateTimeInterface $lastActivityAt): static
    {
        $this->lastActivityAt = $lastActivityAt;
        return $this;
    }

    public function isTyping(): bool
    {
        return $this->isTyping;
    }

    public function setIsTyping(bool $isTyping): static
    {
        $this->isTyping = $isTyping;
        return $this;
    }

    public function getTypingInChatroomId(): ?int
    {
        return $this->typingInChatroomId;
    }

    public function setTypingInChatroomId(?int $typingInChatroomId): static
    {
        $this->typingInChatroomId = $typingInChatroomId;
        return $this;
    }

    public function getTypingStartedAt(): ?\DateTimeInterface
    {
        return $this->typingStartedAt;
    }

    public function setTypingStartedAt(?\DateTimeInterface $typingStartedAt): static
    {
        $this->typingStartedAt = $typingStartedAt;
        return $this;
    }

    /**
     * Update user activity
     */
    public function updateActivity(): static
    {
        $this->lastActivityAt = new \DateTime();
        $this->lastSeenAt = new \DateTime();
        
        // Auto-update status based on activity
        $this->status = 'online';
        
        return $this;
    }

    /**
     * Check if user is online (active in last 5 minutes)
     */
    public function isOnline(): bool
    {
        if (!$this->lastActivityAt) {
            return false;
        }

        $now = new \DateTime();
        $diff = $now->getTimestamp() - $this->lastActivityAt->getTimestamp();
        
        return $diff < 300; // 5 minutes
    }

    /**
     * Get online status
     */
    public function getOnlineStatus(): string
    {
        if (!$this->lastActivityAt) {
            return 'offline';
        }

        $now = new \DateTime();
        $diff = $now->getTimestamp() - $this->lastActivityAt->getTimestamp();
        
        if ($diff < 300) { // 5 minutes
            return 'online';
        } elseif ($diff < 3600) { // 1 hour
            return 'away';
        } else {
            return 'offline';
        }
    }

    /**
     * Get last seen text
     */
    public function getLastSeenText(): string
    {
        if ($this->isOnline()) {
            return 'En ligne';
        }

        if (!$this->lastSeenAt) {
            return 'Jamais vu';
        }

        $now = new \DateTime();
        $diff = $now->getTimestamp() - $this->lastSeenAt->getTimestamp();

        if ($diff < 60) {
            return 'Il y a moins d\'une minute';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "Il y a {$minutes} minute" . ($minutes > 1 ? 's' : '');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "Il y a {$hours} heure" . ($hours > 1 ? 's' : '');
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return "Il y a {$days} jour" . ($days > 1 ? 's' : '');
        } else {
            return 'Il y a longtemps';
        }
    }
}
