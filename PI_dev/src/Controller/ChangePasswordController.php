<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChangePasswordController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/user/change-password', name: 'app_change_password')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encoder le nouveau mot de passe
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $entityManager->flush();

            // Envoyer un email de confirmation
            $emailService->sendPasswordChanged($user->getEmail(), $user->getFirstName());

            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('user/change_password.html.twig', [
            'changePasswordForm' => $form,
        ]);
    }
}
