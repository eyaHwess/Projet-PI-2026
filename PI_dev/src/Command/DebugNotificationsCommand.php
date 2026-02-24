<?php

namespace App\Command;

use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:debug-notifications',
    description: 'Debug notifications for all users',
)]
class DebugNotificationsCommand extends Command
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Debug Notifications');

        // Get all users
        $users = $this->userRepository->findAll();
        
        $io->section('Users in database');
        $io->table(
            ['ID', 'Email', 'First Name', 'Last Name', 'Roles'],
            array_map(fn($user) => [
                $user->getId(),
                $user->getEmail(),
                $user->getFirstName(),
                $user->getLastName(),
                implode(', ', $user->getRoles())
            ], $users)
        );

        // Get all notifications
        $allNotifications = $this->notificationRepository->findAll();
        
        $io->section('All Notifications');
        $io->writeln(sprintf('Total notifications: %d', count($allNotifications)));
        
        if (count($allNotifications) > 0) {
            $io->table(
                ['ID', 'User ID', 'User Email', 'Type', 'Message', 'Is Read', 'Created At'],
                array_map(fn($notif) => [
                    $notif->getId(),
                    $notif->getUser() ? $notif->getUser()->getId() : 'NULL',
                    $notif->getUser() ? $notif->getUser()->getEmail() : 'NULL',
                    $notif->getType(),
                    substr($notif->getMessage(), 0, 50) . '...',
                    $notif->isRead() ? 'Yes' : 'No',
                    $notif->getCreatedAt()->format('Y-m-d H:i:s')
                ], array_slice($allNotifications, 0, 10))
            );
        }

        // Check notifications per user
        $io->section('Notifications per user');
        foreach ($users as $user) {
            $notifications = $this->notificationRepository->findByUser($user);
            $unreadCount = $this->notificationRepository->countUnreadByUser($user);
            
            $io->writeln(sprintf(
                'User: %s (%s) - Total: %d, Unread: %d',
                $user->getEmail(),
                $user->getFirstName() . ' ' . $user->getLastName(),
                count($notifications),
                $unreadCount
            ));
        }

        $io->success('Debug complete!');

        return Command::SUCCESS;
    }
}
