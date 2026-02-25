<?php

namespace App\Command;

use App\Service\Moderation\ModerationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-moderation',
    description: 'Test the Google Perspective API moderation service',
)]
class TestModerationCommand extends Command
{
    public function __construct(
        private ModerationService $moderationService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Testing Google Perspective API Moderation');

        // Test 1: Clean content
        $io->section('Test 1: Clean Content');
        $cleanText = "This is a wonderful day and I love technology!";
        $io->text("Testing: \"$cleanText\"");
        
        $result1 = $this->moderationService->analyzeContent($cleanText);
        $io->text("Is Clean: " . ($result1->isClean() ? 'YES' : 'NO'));
        foreach ($result1->getScores() as $attribute => $score) {
            $io->text("$attribute: $score");
        }        
        // Test 2: Toxic content
        $io->section('Test 2: Toxic Content');
        $toxicText = "You are an idiot and I hate you";
        $io->text("Testing: \"$toxicText\"");
        
        $result2 = $this->moderationService->analyzeContent($toxicText);
        $io->text("Is Clean: " . ($result2->isClean() ? 'YES' : 'NO'));
        $io->text("Scores: " . json_encode($result2->getScores(), JSON_PRETTY_PRINT));
        $io->text("Flagged: " . implode(', ', $result2->getFlaggedAttributes()));
        $io->text("Message: " . $result2->getMessage());

        // Test 3: Profanity
        $io->section('Test 3: Profanity');
        $profaneText = "This is fucking terrible";
        $io->text("Testing: \"$profaneText\"");
        
        $result3 = $this->moderationService->analyzeContent($profaneText);
        $io->text("Is Clean: " . ($result3->isClean() ? 'YES' : 'NO'));
        $io->text("Scores: " . json_encode($result3->getScores(), JSON_PRETTY_PRINT));
        if (!$result3->isClean()) {
            $io->text("Flagged: " . implode(', ', $result3->getFlaggedAttributes()));
            $io->text("Message: " . $result3->getMessage());
        }

        $io->success('Moderation test completed!');

        return Command::SUCCESS;
    }
}
