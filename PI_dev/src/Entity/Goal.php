<?php

namespace App\Entity;

use App\Repository\GoalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GoalRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Goal
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

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de début est obligatoire.')]
    #[Assert\Type(\DateTimeInterface::class, message: 'La date de début doit être une date valide.')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de fin est obligatoire.')]
    #[Assert\Type(\DateTimeInterface::class, message: 'La date de fin doit être une date valide.')]
    #[Assert\GreaterThan(
        propertyPath: 'startDate',
        message: 'La date de fin doit être postérieure à la date de début.'
    )]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(
        choices: ['draft', 'active', 'paused', 'completed', 'failed', 'archived'],
        message: 'Le statut doit être : draft, active, paused, completed, failed ou archived.'
    )]
    private ?string $status = 'draft';

    #[ORM\ManyToOne(inversedBy: 'goals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: Routine::class, mappedBy: 'goal', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $routines;

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
        $this->routines = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Routine>
     */
    public function getRoutines(): Collection
    {
        return $this->routines;
    }

    public function addRoutine(Routine $routine): static
    {
        if (!$this->routines->contains($routine)) {
            $this->routines->add($routine);
            $routine->setGoal($this);
        }

        return $this;
    }

    public function removeRoutine(Routine $routine): static
    {
        if ($this->routines->removeElement($routine)) {
            // set the owning side to null (unless already changed)
            if ($routine->getGoal() === $this) {
                $routine->setGoal(null);
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

    public function getProgressPercentage(): float
    {
        $totalActivities = 0;
        $completedActivities = 0;

        foreach ($this->routines as $routine) {
            foreach ($routine->getActivities() as $activity) {
                $totalActivities++;
                if ($activity->getStatus() === 'completed') {
                    $completedActivities++;
                }
            }
        }

        if ($totalActivities === 0) {
            return 0;
        }

        return round(($completedActivities / $totalActivities) * 100, 2);
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
     * Higher score = more urgent
     */
    public function getUrgencyScore(): int
    {
        $score = 0;

        // Priority score (0-30 points)
        if ($this->priority === 'high') {
            $score += 30;
        } elseif ($this->priority === 'medium') {
            $score += 20;
        } elseif ($this->priority === 'low') {
            $score += 10;
        }

        // Deadline proximity score (0-70 points)
        // Utiliser deadline en priorité, sinon endDate
        $dateToCheck = $this->deadline ?? $this->endDate;
        
        if ($dateToCheck) {
            $now = new \DateTime();
            $deadline = \DateTime::createFromInterface($dateToCheck);
            $daysUntilDeadline = $now->diff($deadline)->days;
            $isPast = $now > $deadline;

            if ($isPast) {
                // Overdue - maximum urgency
                $score += 70;
            } elseif ($daysUntilDeadline <= 1) {
                // 1 day or less
                $score += 60;
            } elseif ($daysUntilDeadline <= 3) {
                // 2-3 days
                $score += 50;
            } elseif ($daysUntilDeadline <= 7) {
                // 4-7 days
                $score += 40;
            } elseif ($daysUntilDeadline <= 14) {
                // 8-14 days
                $score += 30;
            } elseif ($daysUntilDeadline <= 30) {
                // 15-30 days
                $score += 20;
            } else {
                // More than 30 days
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
        // Utiliser deadline en priorité, sinon endDate
        $dateToCheck = $this->deadline ?? $this->endDate;
        
        if (!$dateToCheck) {
            return false;
        }

        $now = new \DateTime();
        $deadline = \DateTime::createFromInterface($dateToCheck);
        $daysUntilDeadline = $now->diff($deadline)->days;
        $isPast = $now > $deadline;

        return !$isPast && $daysUntilDeadline <= 7;
    }

    /**
     * Vérifie si le goal est "At Risk" (progression < 40% et deadline proche)
     */
    public function isAtRisk(): bool
    {
        return $this->isDeadlineNear() && $this->getProgressPercentage() < 40;
    }

    /**
     * Calcule le temps restant jusqu'à la deadline
     */
    public function getTimeRemaining(): ?string
    {
        $dateToCheck = $this->deadline ?? $this->endDate;
        
        if (!$dateToCheck) {
            return null;
        }

        $now = new \DateTime();
        $deadline = \DateTime::createFromInterface($dateToCheck);
        $diff = $now->diff($deadline);
        
        // Si la deadline est dépassée
        if ($now > $deadline) {
            if ($diff->days == 0) {
                return "Aujourd'hui";
            } elseif ($diff->days == 1) {
                return "Hier";
            } else {
                return "Il y a " . $diff->days . " jour(s)";
            }
        }
        
        // Si la deadline est dans le futur
        if ($diff->days == 0) {
            return "Aujourd'hui";
        } elseif ($diff->days == 1) {
            return "Demain";
        } elseif ($diff->days <= 7) {
            return "Dans " . $diff->days . " jour(s)";
        } elseif ($diff->days <= 30) {
            $weeks = floor($diff->days / 7);
            return "Dans " . $weeks . " semaine(s)";
        } elseif ($diff->days <= 365) {
            $months = floor($diff->days / 30);
            return "Dans " . $months . " mois";
        } else {
            $years = floor($diff->days / 365);
            return "Dans " . $years . " an(s)";
        }
    }

    /**
     * Obtient la couleur du badge de temps restant
     */
    public function getTimeRemainingColor(): string
    {
        $dateToCheck = $this->deadline ?? $this->endDate;
        
        if (!$dateToCheck) {
            return 'secondary';
        }

        $now = new \DateTime();
        $deadline = \DateTime::createFromInterface($dateToCheck);
        $diff = $now->diff($deadline);
        
        if ($now > $deadline) {
            return 'danger'; // Rouge si dépassé
        } elseif ($diff->days <= 1) {
            return 'danger'; // Rouge si aujourd'hui ou demain
        } elseif ($diff->days <= 7) {
            return 'warning'; // Jaune si dans la semaine
        } else {
            return 'info'; // Bleu si plus loin
        }
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
     * Vérifie et met à jour automatiquement le statut du goal
     */
    public function updateAutoStatus(): void
    {
        // Ne pas modifier si archivé
        if ($this->status === 'archived') {
            return;
        }

        // Vérifier si toutes les routines sont complétées
        $allRoutinesCompleted = true;
        $hasRoutines = false;
        
        foreach ($this->routines as $routine) {
            $hasRoutines = true;
            if ($routine->getStatus() !== 'completed') {
                $allRoutinesCompleted = false;
                break;
            }
        }

        // Si toutes les routines sont complétées, marquer le goal comme completed
        if ($hasRoutines && $allRoutinesCompleted && $this->status !== 'completed') {
            $this->status = 'completed';
            return;
        }

        // Vérifier si deadline dépassée avec progression < 50%
        // Utiliser deadline en priorité, sinon endDate
        $dateToCheck = $this->deadline ?? $this->endDate;
        
        if ($dateToCheck && $this->status !== 'completed') {
            $now = new \DateTime();
            $deadline = \DateTime::createFromInterface($dateToCheck);
            
            if ($now > $deadline && $this->getProgressPercentage() < 50) {
                $this->status = 'failed';
                return;
            }
        }
    }

    /**
     * Vérifie si le goal peut être modifié
     */
    public function canBeModified(): bool
    {
        return !in_array($this->status, ['completed', 'failed', 'archived']);
    }

    /**
     * Vérifie si les activités peuvent être exécutées
     */
    public function canExecuteActivities(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Active le goal (depuis draft)
     */
    public function activate(): void
    {
        if ($this->status === 'draft') {
            $this->status = 'active';
        }
    }

    /**
     * Met en pause le goal
     */
    public function pause(): void
    {
        if ($this->status === 'active') {
            $this->status = 'paused';
        }
    }

    /**
     * Reprend le goal
     */
    public function resume(): void
    {
        if ($this->status === 'paused') {
            $this->status = 'active';
        }
    }

    /**
     * Archive le goal
     */
    public function archive(): void
    {
        $this->status = 'archived';
    }
}
