<?php

namespace App\Tests\Entity;

use App\Entity\Goal;
use App\Entity\Routine;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Routine (module Goals).
 */
class RoutineEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $routine = new Routine();
        $this->assertSame('draft', $routine->getStatus());
        $this->assertSame('private', $routine->getVisibility());
        $this->assertInstanceOf(\DateTimeImmutable::class, $routine->getCreatedAt());
        $this->assertCount(0, $routine->getActivities());
        $this->assertFalse($routine->isFavorite());
    }

    public function testGettersSetters(): void
    {
        $routine = new Routine();
        $routine->setTitle('Routine matinale');
        $routine->setDescription('Description');
        $routine->setStatus('active');
        $routine->setVisibility('public');
        $routine->setPriority('medium');
        $routine->setIsFavorite(true);

        $this->assertSame('Routine matinale', $routine->getTitle());
        $this->assertSame('Description', $routine->getDescription());
        $this->assertSame('active', $routine->getStatus());
        $this->assertSame('public', $routine->getVisibility());
        $this->assertSame('medium', $routine->getPriority());
        $this->assertTrue($routine->isFavorite());
    }

    public function testSetGoal(): void
    {
        $user = new User();
        $user->setEmail('u@test.com');
        $goal = new Goal();
        $goal->setUser($user);
        $goal->setTitle('Goal');
        $goal->setStartDate(new \DateTime());
        $goal->setEndDate(new \DateTime('+1 month'));

        $routine = new Routine();
        $routine->setGoal($goal);
        $this->assertSame($goal, $routine->getGoal());
    }
}
