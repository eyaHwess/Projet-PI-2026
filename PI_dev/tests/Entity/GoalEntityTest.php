<?php

namespace App\Tests\Entity;

use App\Entity\Goal;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Goal (module Goals).
 */
class GoalEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $goal = new Goal();
        $this->assertSame('draft', $goal->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $goal->getCreatedAt());
        $this->assertCount(0, $goal->getRoutines());
        $this->assertFalse($goal->isFavorite());
    }

    public function testGettersSetters(): void
    {
        $goal = new Goal();
        $goal->setTitle('Mon objectif');
        $goal->setDescription('Description courte');
        $goal->setStartDate(new \DateTime('2025-01-01'));
        $goal->setEndDate(new \DateTime('2025-06-01'));
        $goal->setStatus('active');
        $goal->setPriority('high');
        $goal->setIsFavorite(true);
        $goal->setProgress(50);

        $this->assertSame('Mon objectif', $goal->getTitle());
        $this->assertSame('Description courte', $goal->getDescription());
        $this->assertSame('active', $goal->getStatus());
        $this->assertSame('high', $goal->getPriority());
        $this->assertTrue($goal->isFavorite());
        $this->assertSame(50, $goal->getProgress());
    }

    public function testSetUser(): void
    {
        $user = new User();
        $user->setEmail('user@test.com');
        $goal = new Goal();
        $goal->setUser($user);
        $this->assertSame($user, $goal->getUser());
    }
}
