<?php

namespace App\Command;

use App\Entity\TimeSlot;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:populate-timeslots',
    description: 'Génère des créneaux horaires pour les coaches',
)]
class PopulateTimeSlotsCommand extends Command
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

        $coaches = $this->userRepository->findCoaches();

        if (empty($coaches)) {
            $io->error('Aucun coach trouvé. Exécutez d\'abord app:populate-coaches');
            return Command::FAILURE;
        }

        $slotCount = 0;

        foreach ($coaches as $coach) {
            // Générer des créneaux pour les 14 prochains jours
            for ($day = 0; $day < 14; $day++) {
                $date = new \DateTimeImmutable("+{$day} days");
                
                // Créneaux du matin (9h-12h)
                $morningSlots = $this->generateSlotsForPeriod($coach, $date, 9, 12);
                $slotCount += count($morningSlots);
                
                // Créneaux de l'après-midi (14h-18h)
                $afternoonSlots = $this->generateSlotsForPeriod($coach, $date, 14, 18);
                $slotCount += count($afternoonSlots);
                
                // Créneaux du soir (18h-20h) - moins fréquents
                if (rand(0, 2) === 0) { // 33% de chance
                    $eveningSlots = $this->generateSlotsForPeriod($coach, $date, 18, 20);
                    $slotCount += count($eveningSlots);
                }
            }
        }

        $this->entityManager->flush();

        $io->success("{$slotCount} créneaux horaires ont été créés pour " . count($coaches) . " coaches !");

        return Command::SUCCESS;
    }

    private function generateSlotsForPeriod(
        $coach,
        \DateTimeImmutable $date,
        int $startHour,
        int $endHour
    ): array {
        $slots = [];
        
        // Créneaux d'1 heure
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            // 70% de chance d'avoir ce créneau disponible
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
