<?php

namespace App\GoalHistoryBundle\Entity;

use App\Entity\Goal;
use App\Entity\User;
use App\GoalHistoryBundle\Repository\GoalHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a single history entry for a Goal action.
 *
 * Supported action values (non-exhaustive):
 *   goal_created, goal_updated, goal_deleted,
 *   status_changed, milestone_completed,
 *   priority_changed, progress_updated
 */
#[ORM\Entity(repositoryClass: GoalHistoryRepository::class)]
#[ORM\Table(name: 'goal_history')]
#[ORM\Index(columns: ['goal_id'], name: 'idx_goal_history_goal')]
#[ORM\Index(columns: ['user_id'], name: 'idx_goal_history_user')]
#[ORM\Index(columns: ['action'], name: 'idx_goal_history_action')]
#[ORM\Index(columns: ['created_at'], name: 'idx_goal_history_created')]
class GoalHistory
{
    // ── Predefined action constants ──────────────────────────────────────
    public const ACTION_CREATED            = 'goal_created';
    public const ACTION_UPDATED            = 'goal_updated';
    public const ACTION_DELETED            = 'goal_deleted';
    public const ACTION_STATUS_CHANGED     = 'status_changed';
    public const ACTION_PRIORITY_CHANGED   = 'priority_changed';
    public const ACTION_PROGRESS_UPDATED   = 'progress_updated';
    public const ACTION_MILESTONE_DONE     = 'milestone_completed';
    public const ACTION_DEADLINE_SET       = 'deadline_set';
    public const ACTION_FAVORITE_TOGGLED   = 'favorite_toggled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** The goal this history entry belongs to. */
    #[ORM\ManyToOne(targetEntity: Goal::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Goal $goal = null;

    /** The user who performed the action (nullable: system/automated actions). */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /** Short action identifier, e.g. "status_changed", "goal_created". */
    #[ORM\Column(length: 100)]
    private string $action = '';

    /** Previous status value before the action (null for non-status actions). */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $oldStatus = null;

    /** New status value after the action (null for non-status actions). */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $newStatus = null;

    /** Human-readable description / additional context. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /** Extra structured metadata (e.g. changed fields, old/new values). */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // ── Getters / Setters ─────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoal(): ?Goal
    {
        return $this->goal;
    }

    public function setGoal(Goal $goal): static
    {
        $this->goal = $goal;
        return $this;
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

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getOldStatus(): ?string
    {
        return $this->oldStatus;
    }

    public function setOldStatus(?string $oldStatus): static
    {
        $this->oldStatus = $oldStatus;
        return $this;
    }

    public function getNewStatus(): ?string
    {
        return $this->newStatus;
    }

    public function setNewStatus(?string $newStatus): static
    {
        $this->newStatus = $newStatus;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    // ── Helper ────────────────────────────────────────────────────────────

    /** Returns a readable label for the action. */
    public function getActionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED          => 'Objectif créé',
            self::ACTION_UPDATED          => 'Objectif modifié',
            self::ACTION_DELETED          => 'Objectif supprimé',
            self::ACTION_STATUS_CHANGED   => 'Statut modifié',
            self::ACTION_PRIORITY_CHANGED => 'Priorité modifiée',
            self::ACTION_PROGRESS_UPDATED => 'Progression mise à jour',
            self::ACTION_MILESTONE_DONE   => 'Jalon accompli',
            self::ACTION_DEADLINE_SET     => 'Deadline définie',
            self::ACTION_FAVORITE_TOGGLED => 'Favori modifié',
            default                       => $this->action,
        };
    }
}
