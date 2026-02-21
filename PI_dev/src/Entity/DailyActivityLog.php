<?php

namespace App\Entity;

use App\Repository\DailyActivityLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DailyActivityLogRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'idx_user_date', columns: ['user_id', 'log_date'])]
class DailyActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dailyActivityLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $logDate = null;

    #[ORM\Column]
    private ?int $totalActivities = 0;

    #[ORM\Column]
    private ?int $completedActivities = 0;

    #[ORM\Column]
    private ?int $totalRoutines = 0;

    #[ORM\Column]
    private ?int $completedRoutines = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $completionPercentage = '0.00';

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->logDate = new \DateTime();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getLogDate(): ?\DateTimeInterface
    {
        return $this->logDate;
    }

    public function setLogDate(\DateTimeInterface $logDate): static
    {
        $this->logDate = $logDate;
        return $this;
    }

    public function getTotalActivities(): ?int
    {
        return $this->totalActivities;
    }

    public function setTotalActivities(int $totalActivities): static
    {
        $this->totalActivities = $totalActivities;
        return $this;
    }

    public function getCompletedActivities(): ?int
    {
        return $this->completedActivities;
    }

    public function setCompletedActivities(int $completedActivities): static
    {
        $this->completedActivities = $completedActivities;
        return $this;
    }

    public function getTotalRoutines(): ?int
    {
        return $this->totalRoutines;
    }

    public function setTotalRoutines(int $totalRoutines): static
    {
        $this->totalRoutines = $totalRoutines;
        return $this;
    }

    public function getCompletedRoutines(): ?int
    {
        return $this->completedRoutines;
    }

    public function setCompletedRoutines(int $completedRoutines): static
    {
        $this->completedRoutines = $completedRoutines;
        return $this;
    }

    public function getCompletionPercentage(): ?string
    {
        return $this->completionPercentage;
    }

    public function setCompletionPercentage(string $completionPercentage): static
    {
        $this->completionPercentage = $completionPercentage;
        return $this;
    }

    public function calculateCompletionPercentage(): void
    {
        if ($this->totalActivities > 0) {
            $percentage = ($this->completedActivities / $this->totalActivities) * 100;
            $this->completionPercentage = number_format($percentage, 2);
        } else {
            $this->completionPercentage = '0.00';
        }
    }

    public function getHeatmapColor(): string
    {
        $percentage = (float) $this->completionPercentage;
        
        if ($percentage == 0) {
            return '#ef4444'; // Red
        } elseif ($percentage < 50) {
            return '#86efac'; // Light Green
        } elseif ($percentage < 80) {
            return '#22c55e'; // Medium Green
        } else {
            return '#15803d'; // Dark Green
        }
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
