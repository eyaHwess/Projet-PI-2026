<?php

namespace App\Command;

use App\Service\TranslationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-translation',
    description: 'Test the translation service with a given text',
)]
class TestTranslationCommand extends Command
{
    public function __construct(
        private TranslationService $translationService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('text', InputArgument::OPTIONAL, 'Text to translate', 'hello')
            ->addArgument('target', InputArgument::OPTIONAL, 'Target language', 'fr')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $text = $input->getArgument('text');
        $target = $input->getArgument('target');

        $io->title('🌍 Test de Traduction');
        
        $io->section('Configuration');
        $io->text([
            "Texte à traduire: <info>$text</info>",
            "Langue cible: <info>$target</info>",
            "Fournisseur: <info>" . $this->translationService->getProvider() . "</info>"
        ]);

        $io->section('Test de traduction');
        
        try {
            $io->text('⏳ Traduction en cours...');
            
            $result = $this->translationService->translate($text, $target);
            
            $io->success("✅ Traduction réussie!");
            $io->text([
                "Texte original: <comment>$text</comment>",
                "Traduction: <info>$result</info>",
                "Langue cible: <info>$target</info>"
            ]);
            
            // Test avec d'autres exemples
            $io->section('Tests supplémentaires');
            
            $tests = [
                ['hello', 'fr'],
                ['good morning', 'fr'],
                ['bonjour', 'en'],
                ['how are you?', 'fr'],
            ];
            
            foreach ($tests as [$testText, $testLang]) {
                try {
                    $testResult = $this->translationService->translate($testText, $testLang);
                    $io->text("✅ <comment>$testText</comment> → <info>$testResult</info> ($testLang)");
                } catch (\Exception $e) {
                    $io->text("❌ <comment>$testText</comment> → <error>Erreur: " . $e->getMessage() . "</error>");
                }
            }
            
            // Afficher les langues supportées
            $io->section('Langues supportées');
            $languages = $this->translationService->getSupportedLanguages();
            $io->text("Nombre de langues: <info>" . count($languages) . "</info>");
            
            $mainLanguages = ['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية'];
            foreach ($mainLanguages as $code => $name) {
                $available = isset($languages[$code]) ? '✅' : '❌';
                $io->text("$available $code: $name");
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error("❌ Erreur lors de la traduction: " . $e->getMessage());
            $io->text("Trace: " . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}