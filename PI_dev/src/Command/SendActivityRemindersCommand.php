<?php

namespace App\Command;

use App\Service\ActivityReminderService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reminders:send',
    description: 'Envoie les rappels d\'activités dont l\'heure est arrivée.',
)]
class SendActivityRemindersCommand extends Command
{
    public function __construct(
        private ActivityReminderService $reminderService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Envoi des rappels d\'activités');

        $count = $this->reminderService->processPendingReminders();

        if ($count === 0) {
            $io->success('Aucun rappel à envoyer pour l\'instant.');
        } else {
            $io->success(sprintf('%d rappel(s) envoyé(s) avec succès.', $count));
        }

        return Command::SUCCESS;
    }
}
