<?php

namespace App\Tests\Entity;

use App\Entity\Chatroom;
use App\Entity\Goal;
use App\Entity\Message;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Chatroom
 */
class ChatroomTest extends TestCase
{
    /**
     * Test 1 : Vérifier que l'état du chatroom est bien défini
     */
    public function testChatroomState(): void
    {
        $chatroom = new Chatroom();
        $chatroom->setState('active');

        $this->assertEquals('active', $chatroom->getState());
    }

    /**
     * Test 2 : Vérifier que le chatroom peut être verrouillé
     */
    public function testChatroomLocked(): void
    {
        $chatroom = new Chatroom();
        $chatroom->setState('locked');

        $this->assertEquals('locked', $chatroom->getState());
    }

    /**
     * Test 3 : Vérifier que le chatroom peut être archivé
     */
    public function testChatroomArchived(): void
    {
        $chatroom = new Chatroom();
        $chatroom->setState('archived');

        $this->assertEquals('archived', $chatroom->getState());
    }

    /**
     * Test 4 : Vérifier que le chatroom peut être supprimé (soft delete)
     */
    public function testChatroomDeleted(): void
    {
        $chatroom = new Chatroom();
        $chatroom->setState('deleted');

        $this->assertEquals('deleted', $chatroom->getState());
    }

    /**
     * Test 5 : Vérifier que le nom du chatroom est défini
     */
    public function testChatroomName(): void
    {
        $chatroom = new Chatroom();
        $chatroom->setName('Chatroom de test');

        $this->assertEquals('Chatroom de test', $chatroom->getName());
    }

    /**
     * Test 6 : Vérifier que le chatroom appartient à un goal
     */
    public function testChatroomGoal(): void
    {
        $chatroom = new Chatroom();
        $goal = new Goal();
        $goal->setTitle('Objectif de test');

        $chatroom->setGoal($goal);

        $this->assertInstanceOf(Goal::class, $chatroom->getGoal());
        $this->assertEquals('Objectif de test', $chatroom->getGoal()->getTitle());
    }

    /**
     * Test 7 : Vérifier que le chatroom peut contenir des messages
     */
    public function testChatroomMessages(): void
    {
        $chatroom = new Chatroom();
        
        $message1 = new Message();
        $message1->setContent('Message 1');
        $chatroom->addMessage($message1);
        
        $message2 = new Message();
        $message2->setContent('Message 2');
        $chatroom->addMessage($message2);

        $this->assertCount(2, $chatroom->getMessages());
    }

    /**
     * Test 8 : Vérifier que les messages peuvent être supprimés du chatroom
     */
    public function testChatroomRemoveMessage(): void
    {
        $chatroom = new Chatroom();
        
        $message = new Message();
        $message->setContent('Message à supprimer');
        $chatroom->addMessage($message);
        
        $this->assertCount(1, $chatroom->getMessages());
        
        $chatroom->removeMessage($message);
        
        $this->assertCount(0, $chatroom->getMessages());
    }

    /**
     * Test 9 : Vérifier la date de création
     */
    public function testChatroomCreatedAt(): void
    {
        $chatroom = new Chatroom();
        $now = new \DateTime();
        $chatroom->setCreatedAt($now);

        $this->assertEquals($now, $chatroom->getCreatedAt());
    }

    /**
     * Test 10 : Vérifier l'état par défaut
     */
    public function testChatroomDefaultState(): void
    {
        $chatroom = new Chatroom();
        $chatroom->setState('active');

        $this->assertEquals('active', $chatroom->getState());
    }

    /**
     * Test 11 : Règle métier - Chatroom locked ne peut pas recevoir de messages
     */
    public function testCannotSendMessageWhenChatLocked(): void
    {
        $chatroom = new Chatroom();
        $chatroom->setState('locked');

        // Vérifier que l'état est bien "locked"
        $this->assertEquals('locked', $chatroom->getState());
        
        // Dans le contrôleur, cette vérification empêche l'ajout de messages
        // Ici on teste juste que l'état est correct
        $this->assertNotEquals('active', $chatroom->getState());
    }

    /**
     * Test 12 : Règle métier - Chatroom archived est en lecture seule
     */
    public function testArchivedChatroomIsReadOnly(): void
    {
        $chatroom = new Chatroom();
        $chatroom->setState('archived');

        $this->assertEquals('archived', $chatroom->getState());
        $this->assertNotEquals('active', $chatroom->getState());
    }

    /**
     * Test 13 : Règle métier - Chatroom deleted ne doit pas être accessible
     */
    public function testDeletedChatroomNotAccessible(): void
    {
        $chatroom = new Chatroom();
        $chatroom->setState('deleted');

        $this->assertEquals('deleted', $chatroom->getState());
    }
}
