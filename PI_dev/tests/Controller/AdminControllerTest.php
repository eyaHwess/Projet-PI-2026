<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminControllerTest extends WebTestCase
{
    private const ADMIN_TEST_EMAIL = 'admin-test@example.com';

    public function testIndex(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        $userRepository = $container->get(UserRepository::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $adminUser = $userRepository->findOneBy(['email' => self::ADMIN_TEST_EMAIL]);
        if (null === $adminUser) {
            $adminUser = new User();
            $adminUser->setFirstName('Admin');
            $adminUser->setLastName('Test');
            $adminUser->setEmail(self::ADMIN_TEST_EMAIL);
            $adminUser->setPassword($passwordHasher->hashPassword($adminUser, 'password'));
            $adminUser->setRole(UserRole::ADMIN);

            $entityManager->persist($adminUser);
            $entityManager->flush();
        }

        $client->loginUser($adminUser);
        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
    }
}
