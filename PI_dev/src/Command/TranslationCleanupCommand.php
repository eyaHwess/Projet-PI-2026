<?php

namespace App\Command;

use App\Repository\MessageTranslationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:translation:cleanup',
    description: 'Supprime les traductions anciennes non utilisées',
)]
class TranslationCleanupCommand extends Command
{
    public function __construct(
        private MessageTranslationRepository $translationRepo
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('days', InputArgument::OPTIONAL, 'Nombre de jours (défaut: 30)', 30)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = (int) $input->getArgument('days');

        $io->title('🧹 Nettoyage des Traductions Anciennes');
        $io->text("Suppression des traductions non utilisées depuis {$days} jours...");

        $deletedCount = $this->translationRepo->deleteOldTranslations($days);

        if ($deletedCount > 0) {
            $io->success("{$deletedCount} traduction(s) supprimée(s) avec succès !");
        } else {
            $io->info('Aucune traduction à supprimer.');
        }

        return Command::SUCCESS;
    }
}
