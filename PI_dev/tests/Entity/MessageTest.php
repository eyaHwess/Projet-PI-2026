<?php

namespace App\Tests\Entity;

use App\Entity\Message;
use App\Entity\User;
use App\Entity\Chatroom;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Message
 */
class MessageTest extends TestCase
{
    /**
     * Test 1 : Vérifier que le contenu du message est bien enregistré
     */
    public function testMessageContent(): void
    {
        $message = new Message();
        $message->setContent("Bonjour, ceci est un test");

        $this->assertEquals("Bonjour, ceci est un test", $message->getContent());
    }

    /**
     * Test 2 : Vérifier que le message peut être créé sans contenu (avec fichier)
     */
    public function testMessageWithoutContent(): void
    {
        $message = new Message();
        $message->setContent(null);

        $this->assertNull($message->getContent());
    }

    /**
     * Test 3 : Vérifier que la date de création est définie
     */
    public function testMessageCreatedAt(): void
    {
        $message = new Message();
        $now = new \DateTime();
        $message->setCreatedAt($now);

        $this->assertEquals($now, $message->getCreatedAt());
    }

    /**
     * Test 4 : Vérifier que le message peut être édité
     */
    public function testMessageIsEdited(): void
    {
        $message = new Message();
        $message->setIsEdited(true);
        $message->setEditedAt(new \DateTime());

        $this->assertTrue($message->getIsEdited());
        $this->assertInstanceOf(\DateTime::class, $message->getEditedAt());
    }

    /**
     * Test 5 : Vérifier que le message peut être épinglé
     */
    public function testMessageIsPinned(): void
    {
        $message = new Message();
        $message->setIsPinned(true);

        $this->assertTrue($message->getIsPinned());
    }

    /**
     * Test 6 : Vérifier les champs de modération
     */
    public function testMessageModeration(): void
    {
        $message = new Message();
        $message->setIsToxic(true);
        $message->setToxicityScore(0.8);
        $message->setModerationStatus('blocked');
        $message->setModerationReason('Contenu toxique détecté');

        $this->assertTrue($message->getIsToxic());
        $this->assertEquals(0.8, $message->getToxicityScore());
        $this->assertEquals('blocked', $message->getModerationStatus());
        $this->assertEquals('Contenu toxique détecté', $message->getModerationReason());
    }

    /**
     * Test 7 : Vérifier que le message peut avoir un auteur
     */
    public function testMessageAuthor(): void
    {
        $message = new Message();
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');

        $message->setAuthor($user);

        $this->assertInstanceOf(User::class, $message->getAuthor());
        $this->assertEquals('John', $message->getAuthor()->getFirstName());
    }

    /**
     * Test 8 : Vérifier que le message appartient à un chatroom
     */
    public function testMessageChatroom(): void
    {
        $message = new Message();
        $chatroom = new Chatroom();
        $chatroom->setName('Test Chatroom');

        $message->setChatroom($chatroom);

        $this->assertInstanceOf(Chatroom::class, $message->getChatroom());
        $this->assertEquals('Test Chatroom', $message->getChatroom()->getName());
    }

    /**
     * Test 9 : Vérifier qu'un message peut répondre à un autre message
     */
    public function testMessageReplyTo(): void
    {
        $originalMessage = new Message();
        $originalMessage->setContent("Message original");

        $replyMessage = new Message();
        $replyMessage->setContent("Réponse au message");
        $replyMessage->setReplyTo($originalMessage);

        $this->assertInstanceOf(Message::class, $replyMessage->getReplyTo());
        $this->assertEquals("Message original", $replyMessage->getReplyTo()->getContent());
    }

    /**
     * Test 10 : Vérifier les valeurs par défaut
     */
    public function testMessageDefaults(): void
    {
        $message = new Message();

        $this->assertFalse($message->getIsEdited());
        $this->assertFalse($message->getIsPinned());
        $this->assertFalse($message->getIsToxic());
        $this->assertFalse($message->getIsSpam());
        $this->assertEquals('approved', $message->getModerationStatus());
    }
}
