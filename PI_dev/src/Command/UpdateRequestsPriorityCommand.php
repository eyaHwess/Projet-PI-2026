<?php

namespace App\Command;

use App\Repository\CoachingRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-requests-priority',
    description: 'Met à jour la priorité de toutes les demandes existantes basée sur leur message',
)]
class UpdateRequestsPriorityCommand extends Command
{
    public function __construct(
        private CoachingRequestRepository $coachingRequestRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Mise à jour des priorités des demandes de coaching');

        // Récupérer toutes les demandes
        $requests = $this->coachingRequestRepository->findAll();
        $total = count($requests);

        if ($total === 0) {
            $io->warning('Aucune demande trouvée.');
            return Command::SUCCESS;
        }

        $io->text("Traitement de {$total} demande(s)...");
        $io->progressStart($total);

        $updated = 0;
        foreach ($requests as $request) {
            $oldPriority = $request->getPriority();
            $request->detectAndSetPriority();
            $newPriority = $request->getPriority();

            if ($oldPriority !== $newPriority) {
                $updated++;
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->success([
            "✅ {$total} demande(s) traitée(s)",
            "📝 {$updated} priorité(s) mise(s) à jour",
        ]);

        return Command::SUCCESS;
    }
}
