<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\UserPresence;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité UserPresence (module User).
 */
class UserPresenceEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $presence = new UserPresence();
        $this->assertSame('offline', $presence->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $presence->getLastSeenAt());
        $this->assertFalse($presence->isTyping());
    }

    public function testGettersSetters(): void
    {
        $presence = new UserPresence();
        $presence->setStatus('online');
        $presence->setIsTyping(true);
        $presence->setTypingInChatroomId(42);

        $this->assertSame('online', $presence->getStatus());
        $this->assertTrue($presence->isTyping());
        $this->assertSame(42, $presence->getTypingInChatroomId());
    }

    public function testSetUser(): void
    {
        $user = new User();
        $user->setEmail('u@test.com');
        $presence = new UserPresence();
        $presence->setUser($user);
        $this->assertSame($user, $presence->getUser());
    }

    public function testUpdateActivity(): void
    {
        $presence = new UserPresence();
        $presence->setStatus('offline');
        $presence->updateActivity();
        $this->assertSame('online', $presence->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $presence->getLastActivityAt());
    }

    public function testGetOnlineStatus(): void
    {
        $presence = new UserPresence();
        $presence->setLastActivityAt(new \DateTime('-10 minutes'));
        $this->assertSame('away', $presence->getOnlineStatus());
    }
}
