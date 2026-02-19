<?php

namespace App\Command;

use App\Entity\Message;
use App\Repository\ChatroomRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-demo-messages',
    description: 'Ajoute des messages de démonstration au chatroom',
)]
class AddDemoMessagesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private ChatroomRepository $chatroomRepository,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer le premier chatroom
        $chatroom = $this->chatroomRepository->findOneBy([]);
        
        if (!$chatroom) {
            $io->error('Aucun chatroom trouvé. Créez d\'abord un goal.');
            return Command::FAILURE;
        }

        // Récupérer les utilisateurs
        $users = $this->userRepository->findAll();
        
        if (count($users) < 2) {
            $io->error('Il faut au moins 2 utilisateurs. Créez-en via /user/add');
            return Command::FAILURE;
        }

        $user1 = $users[0];
        $user2 = $users[1] ?? $users[0];

        // Messages de démonstration
        $demoMessages = [
            ['author' => $user1, 'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'time' => '-2 hours'],
            ['author' => $user2, 'content' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'time' => '-2 hours'],
            ['author' => $user1, 'content' => 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.', 'time' => '-1 hour'],
            ['author' => $user2, 'content' => 'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'time' => '-30 minutes'],
            ['author' => $user1, 'content' => 'Hello, Are you there?', 'time' => '-5 minutes'],
        ];

        foreach ($demoMessages as $data) {
            $message = new Message();
            $message->setAuthor($data['author']);
            $message->setChatroom($chatroom);
            $message->setContent($data['content']);
            $message->setCreatedAt(new \DateTime($data['time']));
            
            $this->em->persist($message);
        }

        $this->em->flush();

        $io->success(sprintf('%d messages de démonstration ajoutés au chatroom!', count($demoMessages)));
        $io->info('Accédez au chatroom pour les voir: /chatroom/' . $chatroom->getId());

        return Command::SUCCESS;
    }
}
