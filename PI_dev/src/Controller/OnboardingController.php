<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\OnboardingType;
use App\Service\AiProfileGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/onboarding', name: 'app_onboarding', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
class OnboardingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AiProfileGenerator $aiProfileGenerator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->isOnboarded()) {
            return $this->redirectToRoute('user_dashboard');
        }

        $form = $this->createForm(OnboardingType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $answers = [
                'goals' => $data['goals'],
                'challenges' => $data['challenges'],
                'motivationStyle' => $data['motivationStyle'],
                'planningStyle' => $data['planningStyle'],
                'interests' => $data['interests'],
            ];

            $user->setOnboardingAnswers($answers);

            $profile = $this->aiProfileGenerator->generateProfile($answers);

            if ($profile !== null) {
                $user->setArchetypeName($profile['archetypeName']);
                $user->setArchetypeDescription($profile['description']);
                $user->setArchetypeShortBio($profile['shortBio']);
                $user->setArchetypeData([
                    'strengths' => $profile['strengths'],
                    'growthAreas' => $profile['growthAreas'],
                    'habitSuggestions' => $profile['habitSuggestions'],
                ]);
            }

            $user->setIsOnboarded(true);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profil créé avec succès. Bienvenue sur DayFlow !');

            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('onboarding/index.html.twig', [
            'form' => $form,
        ]);
    }
}
