<?php

namespace App\Entity;

use App\Repository\RoutineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoutineRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Routine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(
        choices: ['public', 'private'],
        message: 'La visibilité doit être public ou private.'
    )]
    private ?string $visibility = 'private';

    #[ORM\Column(length: 50)]
    #[Assert\Choice(
        choices: ['draft', 'active', 'paused', 'completed', 'skipped'],
        message: 'Le statut doit être : draft, active, paused, completed ou skipped.'
    )]
    private ?string $status = 'draft';

    #[ORM\ManyToOne(inversedBy: 'routines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Goal $goal = null;

    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'routine', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $activities;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Choice(
        choices: ['low', 'medium', 'high'],
        message: 'La priorité doit être : low, medium ou high.'
    )]
    private ?string $priority = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\Type(\DateTimeInterface::class, message: 'La deadline doit être une date valide.')]
    private ?\DateTimeInterface $deadline = null;

    #[ORM\Column]
    private ?bool $isFavorite = false;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->visibility = 'private';
        $this->status = 'draft';
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getGoal(): ?Goal
    {
        return $this->goal;
    }

    public function setGoal(?Goal $goal): static
    {
        $this->goal = $goal;

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
            $activity->setRoutine($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getRoutine() === $this) {
                $activity->setRoutine(null);
            }
        }

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeInterface $deadline): static
    {
        $this->deadline = $deadline;
        return $this;
    }

    /**
     * Calculate urgency score based on priority and deadline proximity
     */
    public function getUrgencyScore(): int
    {
        $score = 0;

        if ($this->priority === 'high') {
            $score += 30;
        } elseif ($this->priority === 'medium') {
            $score += 20;
        } elseif ($this->priority === 'low') {
            $score += 10;
        }

        if ($this->deadline) {
            $now = new \DateTime();
            $deadline = \DateTime::createFromInterface($this->deadline);
            $daysUntilDeadline = $now->diff($deadline)->days;
            $isPast = $now > $deadline;

            if ($isPast) {
                $score += 70;
            } elseif ($daysUntilDeadline <= 1) {
                $score += 60;
            } elseif ($daysUntilDeadline <= 3) {
                $score += 50;
            } elseif ($daysUntilDeadline <= 7) {
                $score += 40;
            } elseif ($daysUntilDeadline <= 14) {
                $score += 30;
            } elseif ($daysUntilDeadline <= 30) {
                $score += 20;
            } else {
                $score += 10;
            }
        }

        return $score;
    }

    /**
     * Check if deadline is approaching (within 7 days)
     */
    public function isDeadlineNear(): bool
    {
        if (!$this->deadline) {
            return false;
        }

        $now = new \DateTime();
        $deadline = \DateTime::createFromInterface($this->deadline);
        $daysUntilDeadline = $now->diff($deadline)->days;
        $isPast = $now > $deadline;

        return !$isPast && $daysUntilDeadline <= 7;
    }
    public function isFavorite(): ?bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(bool $isFavorite): static
    {
        $this->isFavorite = $isFavorite;
        return $this;
    }

    /**
     * Vérifie et met à jour automatiquement le statut de la routine
     */
    public function updateAutoStatus(): void
    {
        // Vérifier si toutes les activités sont complétées
        $allActivitiesCompleted = true;
        $hasActivities = false;
        
        foreach ($this->activities as $activity) {
            $hasActivities = true;
            if ($activity->getStatus() !== 'completed') {
                $allActivitiesCompleted = false;
                break;
            }
        }

        // Si toutes les activités sont complétées, marquer la routine comme completed
        if ($hasActivities && $allActivitiesCompleted && $this->status !== 'completed') {
            $this->status = 'completed';
        }
    }

    /**
     * Vérifie si la routine peut être exécutée
     */
    public function canBeExecuted(): bool
    {
        // La routine ne peut être exécutée que si le goal est actif et la routine est active
        return $this->goal && 
               $this->goal->canExecuteActivities() && 
               $this->status === 'active';
    }

    /**
     * Active la routine
     */
    public function activate(): void
    {
        if ($this->status === 'draft' || $this->status === 'paused') {
            $this->status = 'active';
        }
    }

    /**
     * Met en pause la routine
     */
    public function pause(): void
    {
        if ($this->status === 'active') {
            $this->status = 'paused';
        }
    }
}
