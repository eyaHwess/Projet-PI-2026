<?php

namespace App\Entity;

use App\Repository\GoalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Chatroom;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GoalRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Goal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   #[ORM\OneToOne(mappedBy: 'goal', targetEntity: Chatroom::class, cascade: ['persist', 'remove'])]
    private ?Chatroom $chatroom = null;


    /**
     * @var Collection<int, GoalParticipation>
     */
    #[ORM\OneToMany(targetEntity: GoalParticipation::class, mappedBy: 'goal', cascade: ['persist', 'remove'])]
    private Collection $goalParticipations;

        
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
        choices: ['active', 'completed', 'paused', 'cancelled'],
        message: 'Le statut doit être : active, completed, paused ou cancelled.'
    )]
    private ?string $status = 'active';

    #[ORM\ManyToOne(inversedBy: 'goals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: Routine::class, mappedBy: 'goal', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $routines;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->goalParticipations = new ArrayCollection();
        $this->routines = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'active';
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
     * @return Collection<int, GoalParticipation>
     */
    public function getGoalParticipations(): Collection
    {
        return $this->goalParticipations;
    }

    public function addGoalParticipation(GoalParticipation $goalParticipation): static
    {
        if (!$this->goalParticipations->contains($goalParticipation)) {
            $this->goalParticipations->add($goalParticipation);
            $goalParticipation->setGoal($this);
        }

        return $this;
    }

    public function removeGoalParticipation(GoalParticipation $goalParticipation): static
    {
        if ($this->goalParticipations->removeElement($goalParticipation)) {
            if ($goalParticipation->getGoal() === $this) {
                $goalParticipation->setGoal(null);
            }
        }

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
            if ($routine->getGoal() === $this) {
                $routine->setGoal(null);
            }
        }

        return $this;
    }


    public function getChatroom(): ?Chatroom
    {
        return $this->chatroom;
    }

    public function setChatroom(?Chatroom $chatroom): self
    {
        $this->chatroom = $chatroom;
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

    public function isUserParticipating(User $user): bool
    {
        foreach ($this->goalParticipations as $participation) {
            if ($participation->getUser() === $user) {
                return true;
            }
        }
        return false;
    }

    public function getUserParticipation(User $user): ?GoalParticipation
    {
        foreach ($this->goalParticipations as $participation) {
            if ($participation->getUser() === $user) {
                return $participation;
            }
        }
        return null;
    }

    public function canUserModifyGoal(User $user): bool
    {
        $participation = $this->getUserParticipation($user);
        return $participation && ($participation->isOwner() || $participation->isAdmin());
    }

    public function canUserDeleteGoal(User $user): bool
    {
        $participation = $this->getUserParticipation($user);
        return $participation && $participation->isOwner();
    }

    public function canUserRemoveMembers(User $user): bool
    {
        $participation = $this->getUserParticipation($user);
        return $participation && ($participation->isOwner() || $participation->isAdmin());
    }

    public function getPendingRequests(): Collection
    {
        return $this->goalParticipations->filter(function($participation) {
            return $participation->isPending();
        });
    }

    public function getPendingRequestsCount(): int
    {
        return $this->getPendingRequests()->count();
    }

    public function hasUserRequestedAccess(User $user): bool
    {
        foreach ($this->goalParticipations as $participation) {
            if ($participation->getUser() === $user && $participation->isPending()) {
                return true;
            }
        }
        return false;
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
}
