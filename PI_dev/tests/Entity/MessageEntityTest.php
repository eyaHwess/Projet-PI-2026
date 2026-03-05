<?php

namespace App\Tests\Entity;

use App\Entity\Chatroom;
use App\Entity\Goal;
use App\Entity\Message;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Message (module Chatroom).
 */
class MessageEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $message = new Message();
        $this->assertFalse($message->getIsPinned());
        $this->assertFalse($message->getIsEdited());
        $this->assertSame('approved', $message->getModerationStatus());
        $this->assertCount(0, $message->getReplies());
    }

    public function testGettersSetters(): void
    {
        $message = new Message();
        $message->setContent('Contenu du message');
        $message->setCreatedAt(new \DateTime());
        $message->setIsPinned(true);
        $message->setIsEdited(true);
        $message->setModerationStatus('blocked');

        $this->assertSame('Contenu du message', $message->getContent());
        $this->assertTrue($message->getIsPinned());
        $this->assertTrue($message->getIsEdited());
        $this->assertSame('blocked', $message->getModerationStatus());
    }

    public function testSetAuthorAndChatroom(): void
    {
        $user = new User();
        $user->setEmail('author@test.com');
        $goal = new Goal();
        $goal->setUser($user);
        $goal->setTitle('G');
        $goal->setStartDate(new \DateTime());
        $goal->setEndDate(new \DateTime('+1 month'));
        $chatroom = new Chatroom();
        $chatroom->setGoal($goal);
        $chatroom->setCreatedAt(new \DateTime());

        $message = new Message();
        $message->setAuthor($user);
        $message->setChatroom($chatroom);

        $this->assertSame($user, $message->getAuthor());
        $this->assertSame($chatroom, $message->getChatroom());
    }
}
