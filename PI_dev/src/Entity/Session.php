<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: '`session`')]
class Session
{
    // Constantes de statut
    public const STATUS_SCHEDULING = 'scheduling';
    public const STATUS_PROPOSED_BY_USER = 'proposed_by_user';
    public const STATUS_PROPOSED_BY_COACH = 'proposed_by_coach';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // Constantes de priorité
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'session', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "La session doit être liée à une demande de coaching.")]
    private ?CoachingRequest $coachingRequest = null;

    #[ORM\Column(length: 30)]
    #[Assert\Choice(
        choices: [
            self::STATUS_SCHEDULING,
            self::STATUS_PROPOSED_BY_USER,
            self::STATUS_PROPOSED_BY_COACH,
            self::STATUS_CONFIRMED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ],
        message: "Le statut de la session est invalide."
    )]
    private string $status = self::STATUS_SCHEDULING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $proposedTimeByUser = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $proposedTimeByCoach = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $scheduledAt = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "La durée doit être positive")]
    private ?int $duration = null; // Durée en minutes

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Choice(
        choices: [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH],
        message: "La priorité de la session est invalide."
    )]
    private ?string $priority = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objective = null; // Objectif de la session

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de création de la session est obligatoire.")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le prix doit être positif ou nul')]
    private ?float $price = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Choice(
        choices: [self::PAYMENT_STATUS_PENDING, self::PAYMENT_STATUS_PAID],
        message: 'Le statut de paiement est invalide.'
    )]
    private ?string $paymentStatus = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->paymentStatus = self::PAYMENT_STATUS_PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoachingRequest(): ?CoachingRequest
    {
        return $this->coachingRequest;
    }

    public function setCoachingRequest(?CoachingRequest $coachingRequest): static
    {
        $this->coachingRequest = $coachingRequest;

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

    public function getProposedTimeByUser(): ?\DateTimeImmutable
    {
        return $this->proposedTimeByUser;
    }

    public function setProposedTimeByUser(?\DateTimeImmutable $proposedTimeByUser): static
    {
        $this->proposedTimeByUser = $proposedTimeByUser;

        return $this;
    }

    public function getProposedTimeByCoach(): ?\DateTimeImmutable
    {
        return $this->proposedTimeByCoach;
    }

    public function setProposedTimeByCoach(?\DateTimeImmutable $proposedTimeByCoach): static
    {
        $this->proposedTimeByCoach = $proposedTimeByCoach;

        return $this;
    }

    public function getScheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?\DateTimeImmutable $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    /** The agreed time (scheduledAt or the pending proposal) */
    public function getDisplayTime(): ?\DateTimeImmutable
    {
        return $this->scheduledAt ?? $this->proposedTimeByCoach ?? $this->proposedTimeByUser;
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

    public function getObjective(): ?string
    {
        return $this->objective;
    }

    public function setObjective(?string $objective): static
    {
        $this->objective = $objective;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function isPaid(): bool
    {
        return $this->paymentStatus === self::PAYMENT_STATUS_PAID;
    }
}
