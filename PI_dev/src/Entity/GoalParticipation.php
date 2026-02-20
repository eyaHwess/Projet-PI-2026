<?php

namespace App\Entity;

use App\Repository\GoalParticipationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GoalParticipationRepository::class)]
class GoalParticipation
{
    public const ROLE_MEMBER = 'MEMBER';
    public const ROLE_ADMIN = 'ADMIN';
    public const ROLE_OWNER = 'OWNER';

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'goalParticipations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'goalParticipations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Goal $goal = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 20)]
    private string $role = self::ROLE_MEMBER;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_APPROVED;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getGoal(): ?Goal { return $this->goal; }
    public function setGoal(?Goal $goal): static { $this->goal = $goal; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getRole(): string { return $this->role; }
    public function setRole(string $role): static { 
        if (!in_array($role, [self::ROLE_MEMBER, self::ROLE_ADMIN, self::ROLE_OWNER])) {
            throw new \InvalidArgumentException('Invalid role');
        }
        $this->role = $role; 
        return $this; 
    }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { 
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED])) {
            throw new \InvalidArgumentException('Invalid status');
        }
        $this->status = $status; 
        return $this; 
    }

    public function isMember(): bool { return $this->role === self::ROLE_MEMBER; }
    public function isAdmin(): bool { return $this->role === self::ROLE_ADMIN; }
    public function isOwner(): bool { return $this->role === self::ROLE_OWNER; }
    public function canModerate(): bool { return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_OWNER]); }

    public function isPending(): bool { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function isRejected(): bool { return $this->status === self::STATUS_REJECTED; }
}
