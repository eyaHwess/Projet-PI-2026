<?php

namespace App\Command;

use App\Entity\TimeSlot;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-timeslots-for-coach',
    description: 'Add time slots for a specific coach',
)]
class AddTimeSlotsForCoachCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('coachId', InputArgument::REQUIRED, 'Coach ID or email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $coachIdentifier = $input->getArgument('coachId');

        // Try to find coach by ID or email
        if (is_numeric($coachIdentifier)) {
            $coach = $this->userRepository->find($coachIdentifier);
        } else {
            $coach = $this->userRepository->findOneBy(['email' => $coachIdentifier]);
        }

        if (!$coach) {
            $io->error(sprintf('Coach with identifier "%s" not found', $coachIdentifier));
            return Command::FAILURE;
        }

        if (!in_array('ROLE_COACH', $coach->getRoles())) {
            $io->error(sprintf('User "%s" is not a coach', $coach->getEmail()));
            return Command::FAILURE;
        }

        $io->info(sprintf('Generating time slots for coach: %s %s (%s)', 
            $coach->getFirstName(), 
            $coach->getLastName(), 
            $coach->getEmail()
        ));

        $slotCount = 0;

        // Generate slots for the next 14 days
        for ($day = 0; $day < 14; $day++) {
            $date = new \DateTimeImmutable("+{$day} days");
            
            // Morning slots (9h-12h)
            $morningSlots = $this->generateSlotsForPeriod($coach, $date, 9, 12);
            $slotCount += count($morningSlots);
            
            // Afternoon slots (14h-18h)
            $afternoonSlots = $this->generateSlotsForPeriod($coach, $date, 14, 18);
            $slotCount += count($afternoonSlots);
            
            // Evening slots (18h-20h) - less frequent
            if (rand(0, 2) === 0) { // 33% chance
                $eveningSlots = $this->generateSlotsForPeriod($coach, $date, 18, 20);
                $slotCount += count($eveningSlots);
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d time slots created for coach %s %s!', 
            $slotCount, 
            $coach->getFirstName(), 
            $coach->getLastName()
        ));

        return Command::SUCCESS;
    }

    private function generateSlotsForPeriod(
        $coach,
        \DateTimeImmutable $date,
        int $startHour,
        int $endHour
    ): array {
        $slots = [];
        
        // 1-hour slots
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            // 70% chance of having this slot available
            if (rand(0, 9) < 7) {
                $startTime = $date->setTime($hour, 0);
                $endTime = $date->setTime($hour + 1, 0);
                
                $slot = new TimeSlot();
                $slot->setCoach($coach);
                $slot->setStartTime($startTime);
                $slot->setEndTime($endTime);
                $slot->setIsAvailable(true);
                
                $this->entityManager->persist($slot);
                $slots[] = $slot;
            }
        }
        
        return $slots;
    }
}
