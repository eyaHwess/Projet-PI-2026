<?php

namespace App\Entity;

use App\Entity\User;
use App\Enum\ReclamationStatusEnum;
use App\Enum\ReclamationTypeEnum;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Content cannot be empty.")]
    #[Assert\Length(
        min: 10,
        minMessage: "Reclamation must contain at least {{ limit }} characters."
    )]
private ?string $content = null;


    #[ORM\Column(enumType: ReclamationTypeEnum::class)]
    #[Assert\NotNull(message: "Please select a type.")]
    private ?ReclamationTypeEnum $type = null;


    #[ORM\Column(enumType: ReclamationStatusEnum::class)]
    private ?ReclamationStatusEnum $status = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    // ✅ ONLY ONE relation to User
    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Response>
     */
    #[ORM\OneToMany(
        targetEntity: Response::class,
        mappedBy: 'reclamation',
        orphanRemoval: true,
        cascade: ['persist', 'remove']
    )]
    private Collection $responses;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = ReclamationStatusEnum::PENDING;
        $this->responses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getType(): ?ReclamationTypeEnum
    {
        return $this->type;
    }

    public function setType(ReclamationTypeEnum $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getStatus(): ?ReclamationStatusEnum
    {
        return $this->status;
    }

    public function setStatus(ReclamationStatusEnum $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, Response>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(Response $response): static
    {
        if (!$this->responses->contains($response)) {
            $this->responses->add($response);
            $response->setReclamation($this);
        }

        return $this;
    }

    public function removeResponse(Response $response): static
    {
        if ($this->responses->removeElement($response)) {
            if ($response->getReclamation() === $this) {
                $response->setReclamation(null);
            }
        }

        return $this;
    }
}
