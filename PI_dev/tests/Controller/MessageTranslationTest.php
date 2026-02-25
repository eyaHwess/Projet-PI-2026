<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Goal;
use App\Entity\Chatroom;
use App\Entity\Message;

class MessageTranslationTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Test que la route de traduction existe
     */
    public function testTranslationRouteExists(): void
    {
        $router = $this->client->getContainer()->get('router');
        $route = $router->getRouteCollection()->get('message_translate');
        
        $this->assertNotNull($route, 'La route message_translate doit exister');
        $this->assertEquals(['POST'], $route->getMethods(), 'La route doit accepter POST');
    }

    /**
     * Test de traduction d'un message (simulation)
     */
    public function testTranslateMessageEndpoint(): void
    {
        // Créer un utilisateur de test
        $user = $this->createTestUser();
        
        // Créer un goal et un chatroom de test
        $goal = $this->createTestGoal($user);
        $chatroom = $this->createTestChatroom($goal);
        
        // Créer un message de test
        $message = $this->createTestMessage($chatroom, $user, 'Bonjour, comment allez-vous?');
        
        // Se connecter en tant qu'utilisateur
        $this->client->loginUser($user);
        
        // Appeler la route de traduction
        $this->client->request(
            'POST',
            '/message/' . $message->getId() . '/translate',
            ['lang' => 'en'],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        
        $response = $this->client->getResponse();
        
        // Vérifier que la réponse est OK
        $this->assertEquals(200, $response->getStatusCode(), 'La réponse doit être 200 OK');
        
        // Vérifier que la réponse est du JSON
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            'La réponse doit être du JSON'
        );
        
        // Décoder la réponse
        $data = json_decode($response->getContent(), true);
        
        // Vérifier la structure de la réponse
        $this->assertIsArray($data, 'La réponse doit être un tableau');
        
        // Si pas d'erreur, vérifier les champs
        if (!isset($data['error'])) {
            $this->assertArrayHasKey('translation', $data, 'La réponse doit contenir "translation"');
            $this->assertArrayHasKey('targetLanguage', $data, 'La réponse doit contenir "targetLanguage"');
            $this->assertNotEmpty($data['translation'], 'La traduction ne doit pas être vide');
        }
    }

    /**
     * Test de traduction avec un message vide
     */
    public function testTranslateEmptyMessage(): void
    {
        $user = $this->createTestUser();
        $goal = $this->createTestGoal($user);
        $chatroom = $this->createTestChatroom($goal);
        
        // Créer un message vide
        $message = $this->createTestMessage($chatroom, $user, '');
        
        $this->client->loginUser($user);
        
        $this->client->request(
            'POST',
            '/message/' . $message->getId() . '/translate',
            ['lang' => 'en'],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        // Doit retourner une erreur
        $this->assertArrayHasKey('error', $data, 'Doit retourner une erreur pour un message vide');
    }

    /**
     * Test de traduction sans authentification
     */
    public function testTranslateWithoutAuthentication(): void
    {
        $user = $this->createTestUser();
        $goal = $this->createTestGoal($user);
        $chatroom = $this->createTestChatroom($goal);
        $message = $this->createTestMessage($chatroom, $user, 'Test message');
        
        // Ne pas se connecter
        
        $this->client->request(
            'POST',
            '/message/' . $message->getId() . '/translate',
            ['lang' => 'en']
        );
        
        $response = $this->client->getResponse();
        
        // Doit rediriger vers la page de connexion ou retourner 401
        $this->assertTrue(
            $response->isRedirection() || $response->getStatusCode() === 401,
            'Doit rediriger ou retourner 401 sans authentification'
        );
    }

    /**
     * Test des différentes langues supportées
     */
    public function testDifferentLanguages(): void
    {
        $user = $this->createTestUser();
        $goal = $this->createTestGoal($user);
        $chatroom = $this->createTestChatroom($goal);
        $message = $this->createTestMessage($chatroom, $user, 'Bonjour');
        
        $this->client->loginUser($user);
        
        $languages = ['en', 'fr', 'ar'];
        
        foreach ($languages as $lang) {
            $this->client->request(
                'POST',
                '/message/' . $message->getId() . '/translate',
                ['lang' => $lang],
                [],
                ['HTTP_X-Requested-With' => 'XMLHttpRequest']
            );
            
            $response = $this->client->getResponse();
            
            $this->assertEquals(
                200,
                $response->getStatusCode(),
                "La traduction en $lang doit fonctionner"
            );
        }
    }

    // === Méthodes Helper ===

    private function createTestUser(): User
    {
        $user = new User();
        $user->setEmail('test_translation_' . uniqid() . '@example.com');
        $user->setPassword('password');
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setRoles(['ROLE_USER']);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    private function createTestGoal(User $user): Goal
    {
        $goal = new Goal();
        $goal->setTitle('Test Goal Translation ' . uniqid());
        $goal->setDescription('Test goal for translation');
        $goal->setOwner($user);
        $goal->setStartDate(new \DateTime());
        $goal->setEndDate(new \DateTime('+1 month'));
        $goal->setVisibility('public');
        
        $this->entityManager->persist($goal);
        $this->entityManager->flush();
        
        return $goal;
    }

    private function createTestChatroom(Goal $goal): Chatroom
    {
        $chatroom = new Chatroom();
        $chatroom->setGoal($goal);
        $chatroom->setCreatedAt(new \DateTime());
        $chatroom->setState('active');
        
        $this->entityManager->persist($chatroom);
        $this->entityManager->flush();
        
        return $chatroom;
    }

    private function createTestMessage(Chatroom $chatroom, User $user, string $content): Message
    {
        $message = new Message();
        $message->setChatroom($chatroom);
        $message->setAuthor($user);
        $message->setContent($content);
        $message->setCreatedAt(new \DateTime());
        
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        
        return $message;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Nettoyer la base de données
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
