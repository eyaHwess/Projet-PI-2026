<?php

namespace App\Command;

use App\Entity\GoalParticipation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:assign-roles',
    description: 'Assign roles to goal participants (first participant becomes OWNER, others MEMBER)',
)]
class AssignRolesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $participations = $this->em->getRepository(GoalParticipation::class)->findAll();
        
        // Group by goal
        $participationsByGoal = [];
        foreach ($participations as $participation) {
            $goalId = $participation->getGoal()->getId();
            if (!isset($participationsByGoal[$goalId])) {
                $participationsByGoal[$goalId] = [];
            }
            $participationsByGoal[$goalId][] = $participation;
        }

        $updated = 0;
        foreach ($participationsByGoal as $goalId => $goalParticipations) {
            // Sort by createdAt to find the first participant
            usort($goalParticipations, function($a, $b) {
                return $a->getCreatedAt() <=> $b->getCreatedAt();
            });

            foreach ($goalParticipations as $index => $participation) {
                if ($index === 0) {
                    // First participant becomes OWNER
                    $participation->setRole(GoalParticipation::ROLE_OWNER);
                    $io->info(sprintf(
                        'Goal #%d: %s %s → OWNER',
                        $goalId,
                        $participation->getUser()->getFirstName(),
                        $participation->getUser()->getLastName()
                    ));
                } else {
                    // Others become MEMBER
                    $participation->setRole(GoalParticipation::ROLE_MEMBER);
                    $io->text(sprintf(
                        'Goal #%d: %s %s → MEMBER',
                        $goalId,
                        $participation->getUser()->getFirstName(),
                        $participation->getUser()->getLastName()
                    ));
                }
                $updated++;
            }
        }

        $this->em->flush();

        $io->success(sprintf('Successfully assigned roles to %d participants!', $updated));

        return Command::SUCCESS;
    }
}
