<?php

namespace App\Command;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-test-notifications',
    description: 'Crée des notifications de test pour tous les utilisateurs',
)]
class CreateTestNotificationsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer tous les utilisateurs
        $users = $this->entityManager->getRepository(User::class)->findAll();

        if (empty($users)) {
            $io->error('Aucun utilisateur trouvé');
            return Command::FAILURE;
        }

        $count = 0;
        foreach ($users as $user) {
            // Créer 3 notifications de test pour chaque utilisateur
            $notifications = [
                [
                    'type' => 'request_accepted',
                    'message' => '🎉 Bonne nouvelle ! Votre demande de coaching a été acceptée.'
                ],
                [
                    'type' => 'session_scheduled',
                    'message' => '📅 Votre session de coaching a été planifiée pour demain à 14h.'
                ],
                [
                    'type' => 'request_pending',
                    'message' => '⏰ Votre demande est en attente de réponse du coach.'
                ],
            ];

            foreach ($notifications as $notifData) {
                $notification = new Notification();
                $notification->setUser($user);
                $notification->setType($notifData['type']);
                $notification->setMessage($notifData['message']);
                $notification->setIsRead(false); // NON LUE
                
                $this->entityManager->persist($notification);
                $count++;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d notifications de test créées pour %d utilisateur(s)', $count, count($users)));

        return Command::SUCCESS;
    }
}
