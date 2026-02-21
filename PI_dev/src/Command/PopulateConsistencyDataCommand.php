<?php

namespace App\Command;

use App\Entity\DailyActivityLog;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:populate-consistency-data',
    description: 'Populate test data for consistency heatmap',
)]
class PopulateConsistencyDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get or create static user
        $user = $this->userRepository->findOneBy(['email' => 'static@example.com']);
        
        if (!$user) {
            $io->error('User static@example.com not found. Please create it first.');
            return Command::FAILURE;
        }

        $io->info('Populating consistency data for user: ' . $user->getEmail());

        // Generate data for the last 90 days
        $endDate = new \DateTime();
        $startDate = (clone $endDate)->modify('-90 days');

        $currentDate = clone $startDate;
        $count = 0;

        while ($currentDate <= $endDate) {
            // Check if log already exists
            $existingLog = $this->entityManager->getRepository(DailyActivityLog::class)
                ->findOneBy([
                    'user' => $user,
                    'logDate' => $currentDate
                ]);

            if (!$existingLog) {
                $log = new DailyActivityLog();
                $log->setUser($user);
                $log->setLogDate(clone $currentDate);

                // Generate random but realistic data
                // More activities on weekdays, less on weekends
                $isWeekend = in_array($currentDate->format('N'), [6, 7]);
                
                if ($isWeekend) {
                    $totalActivities = rand(2, 5);
                    $completedActivities = rand(0, $totalActivities);
                    $totalRoutines = rand(1, 3);
                    $completedRoutines = rand(0, $totalRoutines);
                } else {
                    $totalActivities = rand(5, 12);
                    $completedActivities = rand(2, $totalActivities);
                    $totalRoutines = rand(2, 5);
                    $completedRoutines = rand(1, $totalRoutines);
                }

                // Add some variation - some days with no activity
                if (rand(1, 10) <= 2) { // 20% chance of no activity
                    $totalActivities = 0;
                    $completedActivities = 0;
                }

                $log->setTotalActivities($totalActivities);
                $log->setCompletedActivities($completedActivities);
                $log->setTotalRoutines($totalRoutines);
                $log->setCompletedRoutines($completedRoutines);
                $log->calculateCompletionPercentage();

                $this->entityManager->persist($log);
                $count++;

                if ($count % 20 === 0) {
                    $this->entityManager->flush();
                    $io->text("Processed $count days...");
                }
            }

            $currentDate->modify('+1 day');
        }

        $this->entityManager->flush();

        $io->success("Successfully populated $count days of consistency data!");

        return Command::SUCCESS;
    }
}
