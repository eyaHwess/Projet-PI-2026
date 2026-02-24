<?php

namespace App\Entity;

use App\Repository\CoachingRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoachingRequestRepository::class)]
#[ORM\Table(name: 'coaching_request')]
class CoachingRequest
{
    // États du workflow
    public const STATUS_PENDING = 'pending';      // En attente de réponse du coach
    public const STATUS_ACCEPTED = 'accepted';    // Acceptée par le coach
    public const STATUS_PAID = 'paid';            // Paiement effectué
    public const STATUS_CONFIRMED = 'confirmed';  // Session confirmée
    public const STATUS_COMPLETED = 'completed';  // Session terminée
    public const STATUS_CANCELLED = 'cancelled';  // Annulée
    public const STATUS_DECLINED = 'declined';    // Refusée (ancien statut, conservé pour compatibilité)

    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_URGENT = 'urgent';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur qui fait la demande est obligatoire.")]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le coach demandé est obligatoire.")]
    private ?User $coach = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Le message est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: "Le message doit contenir au moins 10 caractères",
        maxMessage: "Le message ne peut pas dépasser 1000 caractères"
    )]
    private ?string $message = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(
        choices: [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
            self::STATUS_PAID,
            self::STATUS_CONFIRMED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_DECLINED
        ],
        message: "Le statut de la demande est invalide."
    )]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de création de la demande est obligatoire.")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $respondedAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $goal = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $level = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $frequency = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    private ?float $budget = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $coachingType = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(
        choices: [self::PRIORITY_NORMAL, self::PRIORITY_MEDIUM, self::PRIORITY_URGENT],
        message: "La priorité de la demande est invalide."
    )]
    private string $priority = self::PRIORITY_NORMAL;

    #[ORM\ManyToOne(targetEntity: TimeSlot::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?TimeSlot $timeSlot = null;

    #[ORM\OneToOne(mappedBy: 'coachingRequest', cascade: ['persist', 'remove'])]
    private ?Session $session = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getCoach(): ?User
    {
        return $this->coach;
    }

    public function setCoach(?User $coach): static
    {
        $this->coach = $coach;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        if (in_array($status, [self::STATUS_ACCEPTED, self::STATUS_DECLINED])) {
            $this->respondedAt = new \DateTimeImmutable();
        }

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRespondedAt(): ?\DateTimeImmutable
    {
        return $this->respondedAt;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): static
    {
        if ($session !== null && $session->getCoachingRequest() !== $this) {
            $session->setCoachingRequest($this);
        }

        $this->session = $session;

        return $this;
    }

    public function getGoal(): ?string
    {
        return $this->goal;
    }

    public function setGoal(?string $goal): static
    {
        $this->goal = $goal;
        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): static
    {
        $this->level = $level;
        return $this;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(?string $frequency): static
    {
        $this->frequency = $frequency;
        return $this;
    }

    public function getBudget(): ?float
    {
        return $this->budget;
    }

    public function setBudget(?float $budget): static
    {
        $this->budget = $budget;
        return $this;
    }

    public function getCoachingType(): ?string
    {
        return $this->coachingType;
    }

    public function setCoachingType(?string $coachingType): static
    {
        $this->coachingType = $coachingType;
        return $this;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function isUrgent(): bool
    {
        return $this->priority === self::PRIORITY_URGENT;
    }

    public function isMedium(): bool
    {
        return $this->priority === self::PRIORITY_MEDIUM;
    }

    public function isNormal(): bool
    {
        return $this->priority === self::PRIORITY_NORMAL;
    }

    /**
     * Détecte automatiquement la priorité basée sur le message
     */
    public function detectAndSetPriority(): static
    {
        $messageLower = strtolower($this->message ?? '');
        
        $urgentKeywords = ['urgent', 'urgence', 'crise', 'choc', 'immédiat', 'critique', 'grave', 'aide', 'sos', 'rapidement', 'vite'];
        $mediumKeywords = ['important', 'bientôt', 'besoin', 'problème', 'difficulté', 'stress', 'anxiété', 'préoccupé'];
        
        foreach ($urgentKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                $this->priority = self::PRIORITY_URGENT;
                return $this;
            }
        }
        
        foreach ($mediumKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                $this->priority = self::PRIORITY_MEDIUM;
                return $this;
            }
        }
        
        $this->priority = self::PRIORITY_NORMAL;
        return $this;
    }

    public function getTimeSlot(): ?TimeSlot
    {
        return $this->timeSlot;
    }

    public function setTimeSlot(?TimeSlot $timeSlot): static
    {
        $this->timeSlot = $timeSlot;
        return $this;
    }
}
