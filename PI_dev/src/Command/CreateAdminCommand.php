<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un compte administrateur (email: admin@gmail.com, mot de passe: admin)',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = 'admin@gmail.com';

        $existing = $this->userRepository->findOneBy(['email' => $email]);
        if ($existing) {
            $io->warning("Un utilisateur avec l'email {$email} existe déjà.");
            $io->table(
                ['Email', 'Rôles'],
                [[$existing->getEmail(), implode(', ', $existing->getRoles())]]
            );
            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Admin');
        $user->setLastName('Admin');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin'));
        $user->setRoles([\App\Enum\UserRole::USER->value, \App\Enum\UserRole::ADMIN->value]);
        $user->setStatus(UserStatus::ACTIVE->value);
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Compte administrateur créé.');
        $io->table(
            ['Champ', 'Valeur'],
            [
                ['Email', $email],
                ['Mot de passe', 'admin'],
                ['Rôles', 'ROLE_USER, ROLE_ADMIN'],
            ]
        );
        $io->note('Connectez-vous avec admin@gmail.com / admin puis accédez à /admin');

        return Command::SUCCESS;
    }
}
