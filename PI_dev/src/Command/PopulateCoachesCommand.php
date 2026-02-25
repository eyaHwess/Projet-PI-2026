<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:populate-coaches',
    description: 'Populate database with sample coaches with enhanced data',
)]
class PopulateCoachesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $coaches = [
            [
                'firstName' => 'Sophie',
                'lastName' => 'Martin',
                'email' => 'sophie.martin@coach.com',
                'speciality' => 'Yoga',
                'rating' => 4.8,
                'reviewCount' => 127,
                'pricePerSession' => 45.0,
                'availability' => 'Disponible',
                'bio' => 'Coach certifiée en Yoga avec 10 ans d\'expérience. Spécialisée dans le Hatha et Vinyasa Yoga.',
                'badges' => ['Top coach', 'Répond rapidement'],
                'respondsQuickly' => true,
                'totalSessions' => 450,
            ],
            [
                'firstName' => 'Thomas',
                'lastName' => 'Dubois',
                'email' => 'thomas.dubois@coach.com',
                'speciality' => 'Musculation',
                'rating' => 4.9,
                'reviewCount' => 203,
                'pricePerSession' => 60.0,
                'availability' => 'Disponible',
                'bio' => 'Coach sportif diplômé d\'État. Expert en prise de masse et préparation physique.',
                'badges' => ['Top coach'],
                'respondsQuickly' => false,
                'totalSessions' => 680,
            ],

            [
                'firstName' => 'Marie',
                'lastName' => 'Leroy',
                'email' => 'marie.leroy@coach.com',
                'speciality' => 'Nutrition',
                'rating' => 4.7,
                'reviewCount' => 89,
                'pricePerSession' => 55.0,
                'availability' => 'Limité',
                'bio' => 'Nutritionniste diplômée. Accompagnement personnalisé pour une alimentation équilibrée.',
                'badges' => ['Répond rapidement'],
                'respondsQuickly' => true,
                'totalSessions' => 320,
            ],
            [
                'firstName' => 'Lucas',
                'lastName' => 'Bernard',
                'email' => 'lucas.bernard@coach.com',
                'speciality' => 'Cardio',
                'rating' => 4.6,
                'reviewCount' => 156,
                'pricePerSession' => 40.0,
                'availability' => 'Disponible',
                'bio' => 'Spécialiste en entraînement cardiovasculaire et perte de poids. Méthodes motivantes et efficaces.',
                'badges' => ['Nouveau'],
                'respondsQuickly' => true,
                'totalSessions' => 280,
            ],
            [
                'firstName' => 'Emma',
                'lastName' => 'Petit',
                'email' => 'emma.petit@coach.com',
                'speciality' => 'Pilates',
                'rating' => 4.9,
                'reviewCount' => 178,
                'pricePerSession' => 50.0,
                'availability' => 'Disponible',
                'bio' => 'Instructrice certifiée Pilates. Focus sur le renforcement musculaire profond et la posture.',
                'badges' => ['Top coach', 'Répond rapidement'],
                'respondsQuickly' => true,
                'totalSessions' => 520,
            ],
            [
                'firstName' => 'Alexandre',
                'lastName' => 'Roux',
                'email' => 'alexandre.roux@coach.com',
                'speciality' => 'CrossFit',
                'rating' => 4.5,
                'reviewCount' => 94,
                'pricePerSession' => 65.0,
                'availability' => 'Limité',
                'bio' => 'Coach CrossFit Level 2. Entraînements intensifs pour améliorer force et endurance.',
                'badges' => [],
                'respondsQuickly' => false,
                'totalSessions' => 210,
            ],
            [
                'firstName' => 'Camille',
                'lastName' => 'Moreau',
                'email' => 'camille.moreau@coach.com',
                'speciality' => 'Yoga',
                'rating' => 4.8,
                'reviewCount' => 142,
                'pricePerSession' => 48.0,
                'availability' => 'Disponible',
                'bio' => 'Professeure de Yoga certifiée. Cours adaptés à tous les niveaux, du débutant à l\'avancé.',
                'badges' => ['Répond rapidement'],
                'respondsQuickly' => true,
                'totalSessions' => 390,
            ],
            [
                'firstName' => 'Hugo',
                'lastName' => 'Simon',
                'email' => 'hugo.simon@coach.com',
                'speciality' => 'Boxe',
                'rating' => 4.7,
                'reviewCount' => 112,
                'pricePerSession' => 55.0,
                'availability' => 'Disponible',
                'bio' => 'Ex-boxeur professionnel. Cours de boxe française et anglaise pour tous niveaux.',
                'badges' => ['Top coach'],
                'respondsQuickly' => false,
                'totalSessions' => 340,
            ],
        ];

        $io->title('Populating coaches with enhanced data');

        foreach ($coaches as $coachData) {
            $existingCoach = $this->userRepository->findOneBy(['email' => $coachData['email']]);
            
            if ($existingCoach) {
                // Update existing coach
                $coach = $existingCoach;
                $io->note('Updating existing coach: ' . $coachData['email']);
            } else {
                // Create new coach
                $coach = new User();
                $coach->setEmail($coachData['email']);
                $coach->setPassword($this->passwordHasher->hashPassword($coach, 'password123'));
                $io->success('Creating new coach: ' . $coachData['email']);
            }

            $coach->setFirstName($coachData['firstName']);
            $coach->setLastName($coachData['lastName']);
            // Set roles using reflection since there's no setRoles method
            $reflection = new \ReflectionClass($coach);
            $property = $reflection->getProperty('roles');
            $property->setAccessible(true);
            $property->setValue($coach, ['ROLE_COACH']);
            $coach->setSpeciality($coachData['speciality']);
            $coach->setRating($coachData['rating']);
            $coach->setReviewCount($coachData['reviewCount']);
            $coach->setPricePerSession($coachData['pricePerSession']);
            $coach->setAvailability($coachData['availability']);
            $coach->setBio($coachData['bio']);
            $coach->setBadges($coachData['badges']);
            $coach->setRespondsQuickly($coachData['respondsQuickly']);
            $coach->setTotalSessions($coachData['totalSessions']);
            $coach->setStatus('active');

            if (!$existingCoach) {
                $this->entityManager->persist($coach);
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('Successfully populated %d coaches!', count($coaches)));
        $io->info('You can now access the enhanced coaches page at /coaches/enhanced');

        return Command::SUCCESS;
    }
}
