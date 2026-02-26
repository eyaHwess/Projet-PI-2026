<?php

namespace App\Command;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\ChatroomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:setup-demo',
    description: 'Crée des utilisateurs et des messages de démonstration',
)]
class SetupDemoDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private ChatroomRepository $chatroomRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Créer les utilisateurs
        $io->section('Création des utilisateurs...');

        $alice = $this->createUser('Alice', 'Dupont', 'alice@test.com', 'password123');
        $bob = $this->createUser('Bob', 'Martin', 'bob@test.com', 'password123');

        $io->success('2 utilisateurs créés!');
        $io->table(
            ['Nom', 'Email', 'Password'],
            [
                ['Alice Dupont', 'alice@test.com', 'password123'],
                ['Bob Martin', 'bob@test.com', 'password123'],
            ]
        );

        // Récupérer le chatroom
        $chatroom = $this->chatroomRepository->findOneBy([]);
        
        if (!$chatroom) {
            $io->error('Aucun chatroom trouvé. Créez d\'abord un goal via /goal/new');
            return Command::FAILURE;
        }

        // Créer les messages
        $io->section('Création des messages...');

        $messages = [
            ['author' => $alice, 'content' => 'Hello everyone! 👋 Ready to start this goal?', 'time' => '-2 hours'],
            ['author' => $bob, 'content' => 'Hi Alice! Yes, I\'m excited to begin. What\'s our first step?', 'time' => '-1 hour 50 minutes'],
            ['author' => $alice, 'content' => 'Great! Let\'s start by setting up our daily routine. I suggest we check in every morning.', 'time' => '-1 hour 30 minutes'],
            ['author' => $bob, 'content' => 'That sounds perfect! I\'ll set a reminder for 9 AM. Should we share our progress here?', 'time' => '-1 hour'],
            ['author' => $alice, 'content' => 'Absolutely! This chatroom is perfect for daily updates and motivation. 💪', 'time' => '-30 minutes'],
            ['author' => $bob, 'content' => 'Awesome! Looking forward to working together on this goal. Let\'s do this! 🚀', 'time' => '-10 minutes'],
        ];

        foreach ($messages as $data) {
            $message = new Message();
            $message->setAuthor($data['author']);
            $message->setChatroom($chatroom);
            $message->setContent($data['content']);
            $message->setCreatedAt(new \DateTime($data['time']));
            
            $this->em->persist($message);
        }

        $this->em->flush();

        $io->success(sprintf('%d messages de démonstration ajoutés!', count($messages)));
        
        $io->section('🎉 Configuration terminée!');
        $io->text([
            'Vous pouvez maintenant:',
            '1. Vous connecter avec alice@test.com ou bob@test.com (password: password123)',
            '2. Accéder au chatroom: /goal/1/messages',
            '3. Voir les messages de démonstration',
            '4. Envoyer vos propres messages!',
        ]);

        return Command::SUCCESS;
    }

    private function createUser(string $firstName, string $lastName, string $email, string $password): User
    {
        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            return $existingUser;
        }

        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        $user->setRoles(['ROLE_USER']);
        $user->setStatus('active');
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
