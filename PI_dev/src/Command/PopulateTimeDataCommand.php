<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:populate-time-data',
    description: 'Populate test time investment data for activities',
)]
class PopulateTimeDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private ActivityRepository $activityRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get static user
        $user = $this->userRepository->findOneBy(['email' => 'static@example.com']);
        
        if (!$user) {
            $io->error('User static@example.com not found. Please create it first.');
            return Command::FAILURE;
        }

        $io->info('Populating time investment data for activities...');

        // Get all activities for this user
        $qb = $this->entityManager->createQueryBuilder();
        $activities = $qb->select('a')
            ->from('App\Entity\Activity', 'a')
            ->join('a.routine', 'r')
            ->join('r.goal', 'g')
            ->where('g.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        if (empty($activities)) {
            $io->warning('No activities found for this user.');
            return Command::SUCCESS;
        }

        $count = 0;
        $now = new \DateTime();

        foreach ($activities as $activity) {
            // Get planned duration from existing duration field
            $plannedMinutes = $activity->getDurationInMinutes();
            
            if ($plannedMinutes > 0) {
                $activity->setPlannedDurationMinutes($plannedMinutes);
                
                // If activity is completed, set actual duration and completed_at
                if ($activity->getStatus() === 'completed') {
                    // Generate realistic actual duration (80% to 130% of planned)
                    $variance = rand(80, 130) / 100;
                    $actualMinutes = (int) ($plannedMinutes * $variance);
                    $activity->setActualDurationMinutes($actualMinutes);
                    
                    // Set completed_at to a random time in the past 60 days
                    $daysAgo = rand(0, 60);
                    $hoursAgo = rand(0, 23);
                    $completedAt = (clone $now)
                        ->modify("-$daysAgo days")
                        ->modify("-$hoursAgo hours");
                    $activity->setCompletedAt($completedAt);
                    
                    $count++;
                }
                
                $this->entityManager->persist($activity);
                
                if ($count % 20 === 0) {
                    $this->entityManager->flush();
                    $io->text("Processed $count completed activities...");
                }
            }
        }

        $this->entityManager->flush();

        $io->success("Successfully populated time data for $count completed activities!");
        $io->info("Total activities processed: " . count($activities));

        return Command::SUCCESS;
    }
}
