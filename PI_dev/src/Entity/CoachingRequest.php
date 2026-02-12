<?php

namespace App\Entity;

use App\Repository\CoachingRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoachingRequestRepository::class)]
#[ORM\Table(name: 'coaching_request')]
class CoachingRequest
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';

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
        choices: [self::STATUS_PENDING, self::STATUS_ACCEPTED, self::STATUS_DECLINED],
        message: "Le statut de la demande est invalide."
    )]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de création de la demande est obligatoire.")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $respondedAt = null;

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
}
