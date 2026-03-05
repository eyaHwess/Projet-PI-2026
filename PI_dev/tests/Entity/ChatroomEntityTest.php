<?php

namespace App\Tests\Entity;

use App\Entity\Chatroom;
use App\Entity\Goal;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Chatroom (module Chatroom).
 */
class ChatroomEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $chatroom = new Chatroom();
        $this->assertSame('active', $chatroom->getState());
        $this->assertCount(0, $chatroom->getMessages());
    }

    public function testGettersSetters(): void
    {
        $chatroom = new Chatroom();
        $chatroom->setState('archived');
        $date = new \DateTime();
        $chatroom->setCreatedAt($date);

        $this->assertSame('archived', $chatroom->getState());
        $this->assertSame($date, $chatroom->getCreatedAt());
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

        $chatroom = new Chatroom();
        $chatroom->setGoal($goal);
        $this->assertSame($goal, $chatroom->getGoal());
    }
}
