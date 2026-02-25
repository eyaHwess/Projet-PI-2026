<?php

namespace App\Entity;

use App\Repository\UserLoginHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserLoginHistoryRepository::class)]
#[ORM\Table(name: 'user_login_history')]
#[ORM\Index(columns: ['logged_at'], name: 'idx_logged_at')]
#[ORM\Index(columns: ['ip_address'], name: 'idx_ip_address')]
class UserLoginHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 45)]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 500)]
    private ?string $userAgent = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $loggedAt = null;

    #[ORM\Column]
    private bool $isSuspicious = false;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $location = null;

    public function __construct()
    {
        $this->loggedAt = new \DateTimeImmutable();
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

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getLoggedAt(): ?\DateTimeImmutable
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(\DateTimeImmutable $loggedAt): static
    {
        $this->loggedAt = $loggedAt;
        return $this;
    }

    public function isSuspicious(): bool
    {
        return $this->isSuspicious;
    }

    public function setIsSuspicious(bool $isSuspicious): static
    {
        $this->isSuspicious = $isSuspicious;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getBrowserName(): string
    {
        $ua = $this->userAgent;
        
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Safari')) return 'Safari';
        if (str_contains($ua, 'Edge')) return 'Edge';
        if (str_contains($ua, 'Opera')) return 'Opera';
        
        return 'Navigateur inconnu';
    }

    public function getDeviceType(): string
    {
        $ua = $this->userAgent;
        
        if (str_contains($ua, 'Mobile')) return 'Mobile';
        if (str_contains($ua, 'Tablet')) return 'Tablette';
        
        return 'Ordinateur';
    }
}
