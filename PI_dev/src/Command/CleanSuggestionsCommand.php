<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clean-suggestions',
    description: 'Remove error messages from suggestions table',
)]
class CleanSuggestionsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🧹 Cleaning Bad Suggestions');

        // Find suggestions with error messages
        $connection = $this->entityManager->getConnection();

        $sql = "SELECT COUNT(*) as count FROM suggestion 
                WHERE content LIKE '%Erreur inattendue%' 
                   OR content LIKE '%HTTP/2 429%'
                   OR content LIKE '%Coaching Temporairement Indisponible%'
                   OR content LIKE '%Erreur d''authentification%'
                   OR content LIKE '%Erreur de connexion%'";

        $result = $connection->executeQuery($sql);
        $count = $result->fetchOne();

        if ($count == 0) {
            $io->success('No bad suggestions found! Database is clean.');
            return Command::SUCCESS;
        }

        $io->warning("Found {$count} suggestion(s) containing error messages");

        if (!$io->confirm('Do you want to delete these suggestions?', true)) {
            $io->info('Operation cancelled');
            return Command::SUCCESS;
        }

        // Delete bad suggestions
        $deleteSql = "DELETE FROM suggestion 
                      WHERE content LIKE '%Erreur inattendue%' 
                         OR content LIKE '%HTTP/2 429%'
                         OR content LIKE '%Coaching Temporairement Indisponible%'
                         OR content LIKE '%Erreur d''authentification%'
                         OR content LIKE '%Erreur de connexion%'";

        $deleted = $connection->executeStatement($deleteSql);

        $io->success("Deleted {$deleted} bad suggestion(s)");

        // Show remaining suggestions
        $remainingSql = "SELECT id, user_id, created_at, LEFT(content, 80) as preview 
                         FROM suggestion 
                         ORDER BY created_at DESC 
                         LIMIT 5";

        $remaining = $connection->executeQuery($remainingSql)->fetchAllAssociative();

        if (count($remaining) > 0) {
            $io->section('Remaining Suggestions');
            $io->table(
                ['ID', 'User ID', 'Created At', 'Preview'],
                array_map(function ($row) {
                    return [
                        $row['id'],
                        $row['user_id'],
                        $row['created_at'],
                        $row['preview'] . '...'
                    ];
                }, $remaining)
            );
        } else {
            $io->info('No suggestions remaining in database');
        }

        return Command::SUCCESS;
    }
}
