<?php

namespace App\Command;

use App\Entity\Activity;
use App\Entity\Chatroom;
use App\Entity\Goal;
use App\Entity\GoalParticipation;
use App\Entity\Reclamation;
use App\Entity\Response as ReclamationResponse;
use App\Entity\Routine;
use App\Entity\User;
use App\Enum\ReclamationStatusEnum;
use App\Enum\ReclamationTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed:demo', description: 'Seed demo goals/routines/activities for user 10 + reclamations for all users')]
class SeedDataCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // ── List users
        $users = $this->em->getRepository(User::class)->findBy([], ['id' => 'ASC']);
        $io->section('Users found: ' . count($users));
        foreach ($users as $u) {
            $io->text($u->getId() . ' | ' . $u->getEmail() . ' | ' . $u->getFirstName());
        }

        // ── User 10
        $user10 = $this->em->getRepository(User::class)->find(10);
        if (!$user10) {
            $io->error('User id=10 not found!');
            return Command::FAILURE;
        }
        $io->success('User 10 found: ' . $user10->getEmail());

        // ── Goals
        $goalsData = [
            ['title' => 'Perdre 5kg en 2 mois',      'desc' => 'Programme de perte de poids progressif avec suivi hebdomadaire.', 'status' => 'active', 'priority' => 'high'],
            ['title' => 'Courir 10km sans pause',     'desc' => "Préparer un semi-marathon en améliorant l'endurance progressivement.", 'status' => 'active', 'priority' => 'medium'],
            ['title' => 'Méditation quotidienne',     'desc' => 'Pratiquer 20 minutes de méditation chaque matin pendant 30 jours.', 'status' => 'paused', 'priority' => 'low'],
        ];

        $createdGoals = [];
        foreach ($goalsData as $gd) {
            $goal = new Goal();
            $goal->setTitle($gd['title']);
            $goal->setDescription($gd['desc']);
            $goal->setIsPublic(false);
            $goal->setStatus($gd['status']);
            $goal->setPriority($gd['priority']);
            $goal->setUser($user10);
            $goal->setStartDate(new \DateTime('-7 days'));
            $goal->setEndDate(new \DateTime('+60 days'));
            $goal->setDeadline(new \DateTime('+60 days'));
            $this->em->persist($goal);

            $chatroom = new Chatroom();
            $chatroom->setCreatedAt(new \DateTime());
            $chatroom->setGoal($goal);
            $this->em->persist($chatroom);

            $part = new GoalParticipation();
            $part->setUser($user10);
            $part->setGoal($goal);
            $part->setRole(GoalParticipation::ROLE_OWNER);
            $part->setStatus(GoalParticipation::STATUS_APPROVED);
            $part->setCreatedAt(new \DateTime());
            $this->em->persist($part);

            $createdGoals[] = $goal;
            $io->text('[GOAL] ' . $gd['title']);
        }
        $this->em->flush();

        // ── Routines + Activities
        $routinesData = [
            0 => [
                ['title' => 'Cardio matin',    'desc' => 'Course légère 30min tous les matins.', 'activities' => [
                    ['title' => 'Course 30min',        'duration' => 30, 'status' => 'completed'],
                    ['title' => 'Étirements',          'duration' => 10, 'status' => 'completed'],
                    ['title' => 'Vélo stationnaire',   'duration' => 25, 'status' => 'pending'],
                ]],
                ['title' => 'Nutrition saine', 'desc' => 'Manger équilibré, éviter le sucre.', 'activities' => [
                    ['title' => 'Préparer repas healthy', 'duration' => 45, 'status' => 'completed'],
                    ['title' => 'Journaliser les repas',  'duration' => 5,  'status' => 'in_progress'],
                ]],
            ],
            1 => [
                ['title' => 'Entraînement course', 'desc' => 'Augmenter la distance chaque semaine.', 'activities' => [
                    ['title' => 'Run 5km',           'duration' => 35, 'status' => 'completed'],
                    ['title' => 'Run 7km',           'duration' => 48, 'status' => 'pending'],
                    ['title' => 'Interval training', 'duration' => 40, 'status' => 'pending'],
                ]],
                ['title' => 'Récupération',        'desc' => 'Stretching et repos actif.', 'activities' => [
                    ['title' => 'Yoga 20min',   'duration' => 20, 'status' => 'completed'],
                    ['title' => 'Foam rolling', 'duration' => 15, 'status' => 'pending'],
                ]],
            ],
            2 => [
                ['title' => 'Méditation guidée', 'desc' => 'Sessions de pleine conscience chaque matin.', 'activities' => [
                    ['title' => 'Méditation 20min',      'duration' => 20, 'status' => 'completed'],
                    ['title' => 'Respiration 4-7-8',     'duration' => 10, 'status' => 'completed'],
                    ['title' => 'Journal de gratitude',  'duration' => 10, 'status' => 'pending'],
                ]],
            ],
        ];

        foreach ($routinesData as $gi => $routinesList) {
            $goal = $createdGoals[$gi];
            foreach ($routinesList as $rd) {
                $routine = new Routine();
                $routine->setTitle($rd['title']);
                $routine->setDescription($rd['desc']);
                $routine->setGoal($goal);
                $routine->setStatus('active');
                $routine->setVisibility('private');
                $routine->setDeadline(new \DateTime('+50 days'));
                $this->em->persist($routine);

                foreach ($rd['activities'] as $ad) {
                    $act = new Activity();
                    // Activity duration is stored as a DateTimeInterface (interval as time)
                    $durationDt = new \DateTime('1970-01-01 00:' . str_pad($ad['duration'], 2, '0', STR_PAD_LEFT) . ':00');
                    $act->setTitle($ad['title']);
                    $act->setDuration($durationDt);
                    $act->setStatus($ad['status']);
                    $act->setRoutine($routine);
                    $act->setPriority('medium');
                    $act->setStartTime(new \DateTime('+1 day'));
                    $act->setHasReminder(false);
                    $this->em->persist($act);
                }

                $io->text('[ROUTINE] ' . $rd['title'] . ' (' . count($rd['activities']) . ' activités)');
            }
        }
        $this->em->flush();
        $io->success('Goals, routines et activités créés pour user 10.');

        // ── Reclamations
        // type => ReclamationTypeEnum, status => ReclamationStatusEnum
        $reclamationsData = [
            ["Problème de connexion",            "Je n'arrive pas à me connecter depuis ce matin, j'ai essayé de réinitialiser mon mot de passe.",                   ReclamationTypeEnum::OTHER,     ReclamationStatusEnum::PENDING],
            ["Bug dans le calendrier",           "Le calendrier n'affiche pas mes activités correctement. Les dates semblent décalées d'un jour.",                    ReclamationTypeEnum::BUG,       ReclamationStatusEnum::IN_PROGRESS],
            ["Suggestion d'amélioration",        "Il serait utile d'avoir une vue hebdomadaire dans le calendrier pour mieux planifier.",                             ReclamationTypeEnum::OTHER,     ReclamationStatusEnum::ANSWERED],
            ["Notification en double",           "Je reçois les mêmes notifications deux fois. C'est assez gênant.",                                                 ReclamationTypeEnum::BUG,       ReclamationStatusEnum::PENDING],
            ["Problème d'upload photo",          "Impossible d'uploader ma photo de profil, j'obtiens une erreur 500.",                                              ReclamationTypeEnum::BUG,       ReclamationStatusEnum::PENDING],
            ["Erreur création d'objectif",       "Quand je tente de créer un objectif avec une deadline passée, l'application plante.",                              ReclamationTypeEnum::BUG,       ReclamationStatusEnum::IN_PROGRESS],
            ["Demande de suppression de compte", "Je souhaite supprimer mon compte et toutes mes données personnelles.",                                              ReclamationTypeEnum::OTHER,     ReclamationStatusEnum::PENDING],
            ["Coach non disponible",             "Le coach que j'ai sélectionné n'est plus disponible mais apparaît toujours dans la liste.",                        ReclamationTypeEnum::COACHING,  ReclamationStatusEnum::CLOSED],
            ["Problème de paiement",             "Mon abonnement premium n'est pas activé malgré le paiement effectué il y a 3 jours.",                             ReclamationTypeEnum::PAYMENT,   ReclamationStatusEnum::PENDING],
            ["Interface en langue étrangère",    "Certaines parties de l'interface s'affichent en anglais alors que j'ai sélectionné le français.",                  ReclamationTypeEnum::BUG,       ReclamationStatusEnum::IN_PROGRESS],
        ];

        // Find admin user — roles is JSON in PostgreSQL, use CAST workaround
        $adminUser = null;
        $allUsersForAdmin = $this->em->getRepository(User::class)->findAll();
        foreach ($allUsersForAdmin as $u) {
            if (in_array('ROLE_ADMIN', $u->getRoles(), true)) {
                $adminUser = $u;
                break;
            }
        }

        foreach ($users as $idx => $user) {
            if ($idx >= count($reclamationsData)) break;
            $rc = $reclamationsData[$idx];

            $rec = new Reclamation();
            $rec->setContent($rc[1]);
            $rec->setType($rc[2]);
            $rec->setStatus($rc[3]);
            $rec->setUser($user);
            $this->em->persist($rec);
            $this->em->flush();

            // Add response for answered/closed ones
            if (in_array($rc[3], [ReclamationStatusEnum::ANSWERED, ReclamationStatusEnum::CLOSED])) {
                $response = new ReclamationResponse();
                $response->setContent('Merci pour votre retour. Le problème a été traité. N\'hésitez pas à nous contacter si le problème persiste.');
                $response->setReclamation($rec);
                $response->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 10) . ' days'));
                $this->em->persist($response);
                $this->em->flush();
            }

            $io->text('[RECLAMATION] ' . $user->getEmail() . ' — ' . $rc[0]);
        }

        $io->success('Reclamations créées pour ' . min(count($users), count($reclamationsData)) . ' utilisateurs.');
        return Command::SUCCESS;
    }
}
