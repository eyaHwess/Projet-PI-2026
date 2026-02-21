<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-coach-role',
    description: 'Ajoute le rôle ROLE_COACH à un utilisateur',
)]
class AddCoachRoleCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error("Utilisateur avec l'email '$email' introuvable.");
            return Command::FAILURE;
        }

        $io->title("Ajout du rôle ROLE_COACH");

        $currentRoles = $user->getRoles();
        $io->info("Rôles actuels : " . implode(', ', $currentRoles));

        if (in_array('ROLE_COACH', $currentRoles)) {
            $io->warning("L'utilisateur a déjà le rôle ROLE_COACH !");
            return Command::SUCCESS;
        }

        // Ajouter le rôle ROLE_COACH
        $roles = $currentRoles;
        $roles[] = 'ROLE_COACH';
        $user->setRoles(array_unique($roles));

        $this->entityManager->flush();

        $io->success("Rôle ROLE_COACH ajouté avec succès !");
        $io->info("Nouveaux rôles : " . implode(', ', $user->getRoles()));
        $io->note("L'utilisateur peut maintenant accéder à /sessions/manage");

        return Command::SUCCESS;
    }
}
