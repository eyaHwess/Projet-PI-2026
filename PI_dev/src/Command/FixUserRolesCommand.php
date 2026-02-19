<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fix-user-roles',
    description: 'Fix user roles in database',
)]
class FixUserRolesCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->userRepository->findAll();
        $fixed = 0;

        foreach ($users as $user) {
            $roles = $user->getRoles();
            
            // Si l'utilisateur n'a pas de rôles ou a des rôles vides
            if (empty($roles) || !in_array('ROLE_USER', $roles)) {
                $io->info("Fixing roles for user: {$user->getEmail()}");
                
                // Récupérer les rôles actuels
                $currentRoles = $roles;
                
                // Ajouter ROLE_USER s'il n'existe pas
                if (!in_array('ROLE_USER', $currentRoles)) {
                    $currentRoles[] = 'ROLE_USER';
                }
                
                // Utiliser reflection pour accéder à la propriété privée
                $reflection = new \ReflectionClass($user);
                $property = $reflection->getProperty('roles');
                $property->setAccessible(true);
                $property->setValue($user, $currentRoles);
                
                $fixed++;
            }
        }

        if ($fixed > 0) {
            $this->entityManager->flush();
            $io->success("Fixed roles for {$fixed} user(s)");
        } else {
            $io->success('All users already have correct roles');
        }

        return Command::SUCCESS;
    }
}
