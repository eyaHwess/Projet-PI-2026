<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Form\RegistrationFormType;
use App\Service\EmailService;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        EmailService $emailService
    ): Response {

        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Hash password
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );

            $user->setPassword($hashedPassword);

            // Rôle : valeur par défaut si vide
            $selectedRole = $form->get('role')->getData() ?? 'ROLE_USER';
            $user->setRole(UserRole::from($selectedRole));

            // Gestion de l'upload de la photo de profil
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                try {
                    $photoFileName = $fileUploader->upload($photoFile);
                    $user->setPhotoUrl('/uploads/profiles/' . $photoFileName);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo: ' . $e->getMessage());
                    return $this->render('security/register.html.twig', [
                        'registrationForm' => $form->createView(),
                    ]);
                }
            }

            if ($selectedRole === 'ROLE_COACH') {
                $user->setSpeciality($form->get('speciality')->getData());
                $user->setAvailability($form->get('availability')->getData());
                $user->setPricePerSession($form->get('pricePerSession')->getData());
                $user->setBio($form->get('bio')->getData());
            }

            try {
                $entityManager->persist($user);
                $entityManager->flush();

                // Envoyer l'email de confirmation
                $emailService->sendRegistrationConfirmation(
                    $user->getEmail(),
                    $user->getFirstName()
                );
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cet email est déjà utilisé. Utilisez un autre email ou connectez-vous.');
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter avec votre email et mot de passe.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
