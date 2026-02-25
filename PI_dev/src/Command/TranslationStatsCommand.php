<?php

namespace App\Command;

use App\Repository\MessageTranslationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:translation:stats',
    description: 'Affiche les statistiques d\'utilisation des traductions',
)]
class TranslationStatsCommand extends Command
{
    public function __construct(
        private MessageTranslationRepository $translationRepo
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('📊 Statistiques des Traductions');

        // Statistiques globales
        $stats = $this->translationRepo->getUsageStats();
        
        if (empty($stats)) {
            $io->warning('Aucune traduction enregistrée pour le moment.');
            return Command::SUCCESS;
        }

        $io->section('📈 Utilisation par Provider et Langue');
        
        $tableData = [];
        $totalTranslations = 0;
        $totalUsage = 0;
        
        foreach ($stats as $stat) {
            $tableData[] = [
                $stat['provider'],
                strtoupper($stat['targetLanguage']),
                $stat['count'],
                $stat['totalUsage'],
            ];
            $totalTranslations += $stat['count'];
            $totalUsage += $stat['totalUsage'];
        }
        
        $io->table(
            ['Provider', 'Langue', 'Traductions', 'Utilisations'],
            $tableData
        );

        $io->section('📊 Résumé');
        $io->listing([
            "Total de traductions uniques : {$totalTranslations}",
            "Total d'utilisations : {$totalUsage}",
            "Taux de réutilisation : " . ($totalTranslations > 0 ? round($totalUsage / $totalTranslations, 2) . 'x' : 'N/A'),
        ]);

        // Traductions les plus utilisées
        $mostUsed = $this->translationRepo->getMostUsedTranslations(5);
        
        if (!empty($mostUsed)) {
            $io->section('🔥 Top 5 des Traductions les Plus Utilisées');
            
            $topTableData = [];
            foreach ($mostUsed as $translation) {
                $originalText = $translation->getMessage()->getContent();
                $translatedText = $translation->getTranslatedText();
                
                // Tronquer si trop long
                $originalText = strlen($originalText) > 40 ? substr($originalText, 0, 40) . '...' : $originalText;
                $translatedText = strlen($translatedText) > 40 ? substr($translatedText, 0, 40) . '...' : $translatedText;
                
                $topTableData[] = [
                    $originalText,
                    $translatedText,
                    strtoupper($translation->getTargetLanguage()),
                    $translation->getUsageCount(),
                ];
            }
            
            $io->table(
                ['Texte Original', 'Traduction', 'Langue', 'Utilisations'],
                $topTableData
            );
        }

        $io->success('Statistiques affichées avec succès !');

        return Command::SUCCESS;
    }
}
