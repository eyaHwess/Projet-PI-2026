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
}
