<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-coach-role',
    description: 'Vérifie et affiche les rôles d\'un utilisateur',
)]
class CheckCoachRoleCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository
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

        $io->title("Informations de l'utilisateur");
        $io->table(
            ['Propriété', 'Valeur'],
            [
                ['Email', $user->getEmail()],
                ['Prénom', $user->getFirstName() ?? 'N/A'],
                ['Nom', $user->getLastName() ?? 'N/A'],
                ['Rôles', implode(', ', $user->getRoles())],
                ['Est Coach?', $user->isCoach() ? 'OUI ✓' : 'NON ✗'],
            ]
        );

        if (!$user->isCoach()) {
            $io->warning("Cet utilisateur n'a PAS le rôle ROLE_COACH !");
            $io->note("Pour ajouter le rôle ROLE_COACH, utilisez la commande :\nphp bin/console app:add-coach-role $email");
        } else {
            $io->success("Cet utilisateur a bien le rôle ROLE_COACH !");
        }

        return Command::SUCCESS;
    }
}
