<?php

namespace App\Command;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Supprime les demandes de réinitialisation de mot de passe pour un utilisateur.
 * Utile si l'email n'a pas été reçu et que le throttle bloque une nouvelle demande.
 */
#[AsCommand(
    name: 'app:reset-password:clear-requests',
    description: 'Supprime les demandes de reset password pour un email donné (pour pouvoir réessayer)',
)]
class ClearResetPasswordRequestsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            $io->warning("Aucun utilisateur trouvé avec l'email : {$email}");
            return Command::FAILURE;
        }

        $repo = $this->entityManager->getRepository(ResetPasswordRequest::class);
        $requests = $repo->findBy(['user' => $user], ['requestedAt' => 'DESC']);
        $count = \count($requests);

        if ($count === 0) {
            $io->success("Aucune demande de reset en attente pour {$email}. Tu peux demander un nouveau lien.");
            return Command::SUCCESS;
        }

        foreach ($requests as $request) {
            $this->entityManager->remove($request);
        }
        $this->entityManager->flush();

        $io->success("{$count} demande(s) de reset supprimée(s) pour {$email}. Tu peux maintenant demander un nouveau lien sur /reset-password.");
        return Command::SUCCESS;
    }
}
