<?php

namespace App\Command;

use App\Repository\TimeSlotRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-coach-timeslots',
    description: 'Check time slots for all coaches',
)]
class CheckCoachTimeSlotsCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private TimeSlotRepository $timeSlotRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Time Slots per Coach');

        // Get all coaches
        $coaches = $this->userRepository->findAll();
        $coaches = array_filter($coaches, fn($user) => in_array('ROLE_COACH', $user->getRoles()));

        $tableData = [];
        foreach ($coaches as $coach) {
            $timeSlots = $this->timeSlotRepository->findBy(['coach' => $coach]);
            $availableSlots = array_filter($timeSlots, fn($slot) => $slot->isAvailable());
            
            $tableData[] = [
                $coach->getId(),
                $coach->getEmail(),
                $coach->getFirstName() . ' ' . $coach->getLastName(),
                count($timeSlots),
                count($availableSlots),
            ];
        }

        // Sort by total slots descending
        usort($tableData, fn($a, $b) => $b[3] <=> $a[3]);

        $io->table(
            ['ID', 'Email', 'Name', 'Total Slots', 'Available Slots'],
            $tableData
        );

        $io->success('Check complete!');

        return Command::SUCCESS;
    }
}
