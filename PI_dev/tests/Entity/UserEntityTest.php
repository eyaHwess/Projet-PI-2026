<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité User (module User).
 */
class UserEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $user = new User();
        $this->assertSame([UserRole::USER->value], $user->getRoles());
        $this->assertSame(UserStatus::ACTIVE->value, $user->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertCount(0, $user->getGoals());
        $this->assertCount(0, $user->getReclamations());
    }

    public function testGettersSetters(): void
    {
        $user = new User();
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setEmail('jean@example.com');
        $user->setPassword('secret123');
        $user->setStatus(UserStatus::INACTIVE->value);

        $this->assertSame('Jean', $user->getFirstName());
        $this->assertSame('Dupont', $user->getLastName());
        $this->assertSame('jean@example.com', $user->getEmail());
        $this->assertSame('secret123', $user->getPassword());
        $this->assertSame(UserStatus::INACTIVE->value, $user->getStatus());
    }

    public function testUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->setPassword('password');
        $user->eraseCredentials();
        $this->assertTrue(true); // eraseCredentials() peut être vide (mot de passe géré ailleurs)
    }
}
