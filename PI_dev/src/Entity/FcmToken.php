<?php

namespace App\Entity;

use App\Repository\FcmTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FcmTokenRepository::class)]
#[ORM\Table(name: 'fcm_token')]
#[ORM\Index(columns: ['user_id'], name: 'idx_fcm_token_user')]
#[ORM\Index(columns: ['token'], name: 'idx_fcm_token_token')]
class FcmToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $token = null;

    #[ORM\Column(length: 50)]
    private ?string $device = null; // web, android, ios

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastUsedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->lastUsedAt = new \DateTimeImmutable();
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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function setDevice(string $device): static
    {
        $this->device = $device;

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

    public function getLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(\DateTimeImmutable $lastUsedAt): static
    {
        $this->lastUsedAt = $lastUsedAt;

        return $this;
    }

    public function updateLastUsed(): static
    {
        $this->lastUsedAt = new \DateTimeImmutable();

        return $this;
    }
}
