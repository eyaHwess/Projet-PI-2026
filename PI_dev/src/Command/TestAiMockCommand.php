<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-ai-mock',
    description: 'Test AI integration with mock data (no API calls)',
)]
class TestAiMockCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🤖 Testing AI Integration (Mock Mode - No API Calls)');

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

        $io->section('Mock AI Suggestion (JSON Format)');

        // This is what the AI would return
        $mockSuggestion = json_encode([
            [
                "title" => "Complete Overdue Goal First",
                "description" => "Focus on finishing your 1 overdue goal within the next 7 days to improve completion consistency",
                "priority" => "high",
                "duration_days" => 7
            ],
            [
                "title" => "Daily 15-Minute Goal Review",
                "description" => "Establish a morning routine to review and prioritize your active goals each day",
                "priority" => "high",
                "duration_days" => 30
            ],
            [
                "title" => "Break Large Goals into Milestones",
                "description" => "Divide your 3 remaining goals into smaller, achievable weekly milestones",
                "priority" => "medium",
                "duration_days" => 14
            ],
            [
                "title" => "Weekly Progress Check-in",
                "description" => "Schedule a 30-minute session every Sunday to assess progress and adjust timelines",
                "priority" => "medium",
                "duration_days" => 90
            ],
            [
                "title" => "Celebrate Small Wins",
                "description" => "Create a reward system for completing milestones to maintain motivation",
                "priority" => "low",
                "duration_days" => 60
            ]
        ], JSON_PRETTY_PRINT);

        $io->success('Mock AI Suggestion Generated Successfully!');
        $io->block($mockSuggestion, null, 'fg=white;bg=blue', ' ', true);

        $io->note('This is mock data. To use real AI, add credits to your OpenAI account.');
        $io->note('Visit: https://platform.openai.com/account/billing');

        return Command::SUCCESS;
    }
}
