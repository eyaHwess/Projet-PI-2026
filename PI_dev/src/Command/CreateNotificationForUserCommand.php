<?php

namespace App\Command;

use App\Entity\Notification;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-notification',
    description: 'Create a test notification for a user',
)]
class CreateNotificationForUserCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('message', InputArgument::OPTIONAL, 'Notification message', 'Test notification');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $message = $input->getArgument('message');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error(sprintf('User with email "%s" not found', $email));
            return Command::FAILURE;
        }

        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType('request_accepted');
        $notification->setMessage($message);
        $notification->setIsRead(false);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $io->success(sprintf('Notification created for user %s (%s)', $user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()));

        return Command::SUCCESS;
    }
}
