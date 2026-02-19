<?php

namespace App\Entity;

use App\Repository\GoalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Chatroom;
#[ORM\Entity(repositoryClass: GoalRepository::class)]
class Goal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $endDate = null;
   #[ORM\OneToOne(mappedBy: 'goal', targetEntity: Chatroom::class, cascade: ['persist', 'remove'])]
private ?Chatroom $chatroom = null;


    /**
     * @var Collection<int, GoalParticipation>
     */
    #[ORM\OneToMany(targetEntity: GoalParticipation::class, mappedBy: 'goal', cascade: ['persist', 'remove'])]
    private Collection $goalParticipations;

    public function __construct()
    {
        $this->goalParticipations = new ArrayCollection();
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

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): static
    {
        $this->startDate = $startDate;

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

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate): static
    {
        $this->endDate = $endDate;

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
    public function getChatroom(): ?Chatroom
    {
        return $this->chatroom;
    }

    public function setChatroom(?Chatroom $chatroom): self
    {
        $this->chatroom = $chatroom;

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

}
