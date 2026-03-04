<?php

namespace App\Entity;

use App\Repository\ChatroomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Goal;


#[ORM\Entity(repositoryClass: ChatroomRepository::class)]
class Chatroom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToOne(inversedBy: 'chatroom', targetEntity: Goal::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Goal $goal = null;

    #[ORM\OneToMany(mappedBy: 'chatroom', targetEntity: Message::class, orphanRemoval: true)]
    private Collection $messages;

    #[ORM\Column(length: 50)]
    private string $state = 'active';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getGoal(): ?Goal { return $this->goal; }
    public function setGoal(?Goal $goal): static { $this->goal = $goal; return $this; }

    public function getMessages(): Collection { return $this->messages; }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setChatroom($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChatroom() === $this) {
                $message->setChatroom(null);
            }
        }

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }
}
