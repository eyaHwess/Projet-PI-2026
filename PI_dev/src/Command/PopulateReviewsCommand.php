<?php

namespace App\Command;

use App\Entity\Review;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:populate-reviews',
    description: 'Peuple la base de données avec des avis de démonstration',
)]
class PopulateReviewsCommand extends Command
{
    private array $reviewTemplates = [
        [
            'rating' => 5,
            'comments' => [
                'Excellent coach ! Très professionnel et à l\'écoute. J\'ai atteint mes objectifs en 3 mois.',
                'Super expérience ! Le coach est motivant et les séances sont bien structurées.',
                'Je recommande vivement ! Résultats visibles rapidement et ambiance agréable.',
                'Coach exceptionnel ! Très pédagogue et adapte les exercices à mon niveau.',
                'Parfait ! J\'ai perdu 10kg grâce à ses conseils personnalisés.',
            ]
        ],
        [
            'rating' => 4.5,
            'comments' => [
                'Très bon coach, quelques petits ajustements à faire mais globalement satisfait.',
                'Bonne expérience, le coach est compétent et sympathique.',
                'Satisfait des résultats, le coach pourrait être un peu plus disponible.',
                'Bon suivi, exercices variés et adaptés. Je recommande.',
            ]
        ],
        [
            'rating' => 4,
            'comments' => [
                'Bon coach dans l\'ensemble, mais les horaires ne sont pas toujours flexibles.',
                'Satisfait mais j\'aurais aimé plus de suivi entre les séances.',
                'Bonne approche pédagogique, résultats corrects.',
            ]
        ],
        [
            'rating' => 3.5,
            'comments' => [
                'Correct mais manque un peu de personnalisation dans les programmes.',
                'Pas mal mais j\'attendais un peu plus de dynamisme.',
            ]
        ],
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer tous les coaches
        $coaches = $this->userRepository->findCoaches();
        
        if (empty($coaches)) {
            $io->error('Aucun coach trouvé. Exécutez d\'abord app:populate-coaches');
            return Command::FAILURE;
        }

        // Récupérer tous les utilisateurs non-coaches pour les avis
        $users = $this->userRepository->findAll();
        $regularUsers = array_filter($users, fn($u) => !$u->isCoach());

        if (empty($regularUsers)) {
            $io->warning('Aucun utilisateur régulier trouvé. Création d\'utilisateurs de test...');
            $regularUsers = $this->createTestUsers();
        }

        $reviewCount = 0;

        foreach ($coaches as $coach) {
            // Nombre aléatoire d'avis par coach (entre 3 et 8)
            $numReviews = rand(3, 8);
            
            // Mélanger les utilisateurs
            shuffle($regularUsers);
            
            for ($i = 0; $i < min($numReviews, count($regularUsers)); $i++) {
                $user = $regularUsers[$i];
                
                // Choisir un template aléatoire
                $template = $this->reviewTemplates[array_rand($this->reviewTemplates)];
                $comment = $template['comments'][array_rand($template['comments'])];
                
                $review = new Review();
                $review->setUser($user);
                $review->setCoach($coach);
                $review->setRating($template['rating']);
                $review->setComment($comment);
                $review->setIsVisible(true);
                $review->setIsVerified(rand(0, 1) === 1); // 50% de chance d'être vérifié
                
                // Date aléatoire dans les 6 derniers mois
                $daysAgo = rand(1, 180);
                $createdAt = new \DateTimeImmutable("-{$daysAgo} days");
                $review->setCreatedAt($createdAt);
                
                $this->entityManager->persist($review);
                $reviewCount++;
            }
        }

        $this->entityManager->flush();

        $io->success("$reviewCount avis ont été créés avec succès !");

        return Command::SUCCESS;
    }

    private function createTestUsers(): array
    {
        $testUsers = [];
        $names = [
            ['Sophie', 'Martin'],
            ['Thomas', 'Bernard'],
            ['Julie', 'Dubois'],
            ['Alexandre', 'Robert'],
            ['Emma', 'Richard'],
        ];

        foreach ($names as [$firstName, $lastName]) {
            $user = new User();
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail(strtolower($firstName) . '.' . strtolower($lastName) . '@example.com');
            $user->setPassword('$2y$13$dummy'); // Mot de passe factice
            $user->setStatus('active');
            
            $this->entityManager->persist($user);
            $testUsers[] = $user;
        }

        $this->entityManager->flush();

        return $testUsers;
    }
}
