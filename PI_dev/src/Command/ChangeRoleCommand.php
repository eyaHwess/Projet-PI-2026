<?php

namespace App\Command;

use App\Entity\GoalParticipation;
use App\Entity\User;
use App\Entity\Goal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:change-role',
    description: 'Change role of a user in a goal',
)]
class ChangeRoleCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('goalId', InputArgument::REQUIRED, 'Goal ID')
            ->addArgument('role', InputArgument::REQUIRED, 'Role (MEMBER, ADMIN, or OWNER)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $goalId = $input->getArgument('goalId');
        $role = strtoupper($input->getArgument('role'));

        // Validate role
        if (!in_array($role, ['MEMBER', 'ADMIN', 'OWNER'])) {
            $io->error('Invalid role. Must be MEMBER, ADMIN, or OWNER');
            return Command::FAILURE;
        }

        // Find user
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            $io->error("User with email '$email' not found");
            return Command::FAILURE;
        }

        // Find goal
        $goal = $this->em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            $io->error("Goal with ID '$goalId' not found");
            return Command::FAILURE;
        }

        // Find participation
        $participation = $this->em->getRepository(GoalParticipation::class)->findOneBy([
            'user' => $user,
            'goal' => $goal
        ]);

        if (!$participation) {
            $io->error("User is not a participant of this goal");
            return Command::FAILURE;
        }

        $oldRole = $participation->getRole();
        $participation->setRole($role);
        $this->em->flush();

        $io->success(sprintf(
            'Changed role for %s %s in goal "%s" from %s to %s',
            $user->getFirstName(),
            $user->getLastName(),
            $goal->getTitle(),
            $oldRole,
            $role
        ));

        return Command::SUCCESS;
    }
}
