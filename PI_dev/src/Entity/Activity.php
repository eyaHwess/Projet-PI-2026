<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Activity
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'L\'heure de début est obligatoire.')]
    #[Assert\Type(\DateTimeInterface::class, message: 'L\'heure de début doit être une date/heure valide.')]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank(message: 'La durée est obligatoire.')]
    #[Assert\Type(\DateTimeInterface::class, message: 'La durée doit être une heure valide.')]
    private ?\DateTimeInterface $duration = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(
        choices: ['pending', 'in_progress', 'completed', 'skipped', 'cancelled'],
        message: 'Le statut doit être : pending, in_progress, completed, skipped ou cancelled.'
    )]
    private ?string $status = 'pending';

    #[ORM\Column]
    private ?bool $hasReminder = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\Type(\DateTimeInterface::class, message: 'La date/heure de rappel doit être une date/heure valide.')]
    private ?\DateTimeInterface $reminderAt = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Routine $routine = null;

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

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $actualDurationMinutes = null;

    #[ORM\Column(nullable: true)]
    private ?int $plannedDurationMinutes = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'pending';
        $this->hasReminder = false;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[Assert\IsTrue(message: 'Si un rappel est activé, la date/heure de rappel doit être définie.')]
    public function isReminderValid(): bool
    {
        return !$this->hasReminder || $this->reminderAt !== null;
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

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getDuration(): ?\DateTimeInterface
    {
        return $this->duration;
    }

    public function setDuration(\DateTimeInterface $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDurationInMinutes(): int
    {
        if (!$this->duration) {
            return 0;
        }

        // duration is a TIME type, so we extract hours and minutes
        $hours = (int) $this->duration->format('H');
        $minutes = (int) $this->duration->format('i');
        
        return ($hours * 60) + $minutes;
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

    public function isHasReminder(): ?bool
    {
        return $this->hasReminder;
    }

    public function setHasReminder(bool $hasReminder): static
    {
        $this->hasReminder = $hasReminder;

        return $this;
    }

    public function getReminderAt(): ?\DateTimeInterface
    {
        return $this->reminderAt;
    }

    public function setReminderAt(?\DateTimeInterface $reminderAt): static
    {
        $this->reminderAt = $reminderAt;

        return $this;
    }

    public function getRoutine(): ?Routine
    {
        return $this->routine;
    }

    public function setRoutine(?Routine $routine): static
    {
        $this->routine = $routine;

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

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getActualDurationMinutes(): ?int
    {
        return $this->actualDurationMinutes;
    }

    public function setActualDurationMinutes(?int $actualDurationMinutes): static
    {
        $this->actualDurationMinutes = $actualDurationMinutes;
        return $this;
    }

    public function getPlannedDurationMinutes(): ?int
    {
        return $this->plannedDurationMinutes;
    }

    public function setPlannedDurationMinutes(?int $plannedDurationMinutes): static
    {
        $this->plannedDurationMinutes = $plannedDurationMinutes;
        return $this;
    }

    /**
     * Calculate time efficiency (actual vs planned)
     * Returns percentage: 100% = on time, >100% = took longer, <100% = finished faster
     */
    public function getTimeEfficiency(): ?float
    {
        if (!$this->plannedDurationMinutes || $this->plannedDurationMinutes === 0) {
            return null;
        }

        if (!$this->actualDurationMinutes) {
            return null;
        }

        return round(($this->actualDurationMinutes / $this->plannedDurationMinutes) * 100, 2);
    }

    /**
     * Check if activity was completed efficiently (within 110% of planned time)
     */
    public function isCompletedEfficiently(): bool
    {
        $efficiency = $this->getTimeEfficiency();
        return $efficiency !== null && $efficiency <= 110;
    }
}
