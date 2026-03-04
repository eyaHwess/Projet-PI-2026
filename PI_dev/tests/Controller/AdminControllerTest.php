<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        
        // Get or create a real admin user from database
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'admin@test.com']);
        
        if (!$testUser) {
            // Create and persist a real user if it doesn't exist
            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $testUser = new User();
            $testUser->setEmail('admin@test.com');
            $testUser->setFirstName('Admin');
            $testUser->setLastName('Test');
            $testUser->setPassword('$2y$13$hashedpassword'); // Dummy hashed password
            $testUser->setRoles(['ROLE_ADMIN']);
            
            $entityManager->persist($testUser);
            $entityManager->flush();
        }
        
        // Login the user
        $client->loginUser($testUser);
        
        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
    }
}
