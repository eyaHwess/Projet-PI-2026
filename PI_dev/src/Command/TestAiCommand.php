<?php

namespace App\Command;

use App\Service\AiAssistantService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-ai',
    description: 'Test OpenAI integration without hitting rate limits',
)]
class TestAiCommand extends Command
{
    public function __construct(
        private AiAssistantService $aiService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🤖 Testing OpenAI Integration');

        $userData = [
            'total_goals' => 5,
            'completed_goals' => 2,
            'overdue_goals' => 1,
            'completion_rate' => 40
        ];

        $io->section('User Statistics');
        $io->table(
            ['Metric', 'Value'],
            [
                ['Total Goals', $userData['total_goals']],
                ['Completed Goals', $userData['completed_goals']],
                ['Overdue Goals', $userData['overdue_goals']],
                ['Completion Rate', $userData['completion_rate'] . '%'],
            ]
        );

        $io->section('Generating AI Suggestion...');

        try {
            $suggestion = $this->aiService->generateSuggestion($userData, 'en');

            if ($suggestion === null) {
                $io->warning('AI service returned null - likely rate limited or API error');
                $io->note('You are probably hitting OpenAI rate limits (3 requests/minute on free tier)');
                $io->note('Wait 60 seconds and try again, or visit /goals page which uses cached suggestions');
                return Command::FAILURE;
            }

            $io->success('AI Suggestion Generated Successfully!');
            $io->block($suggestion, null, 'fg=white;bg=blue', ' ', true);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to generate AI suggestion');
            $io->writeln('Error: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
