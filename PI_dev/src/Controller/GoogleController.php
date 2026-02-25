<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\GoogleConnectAuthenticator;

#[Route('/connect')]
class GoogleController extends AbstractController
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserAuthenticatorInterface $userAuthenticator,
        private GoogleConnectAuthenticator $googleConnectAuthenticator,
    ) {
    }

    /**
     * Redirige vers Google pour l'authentification.
     */
    #[Route('/google', name: 'connect_google', methods: ['GET'])]
    public function connectGoogle(): RedirectResponse|Response
    {
        $clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        if ($clientId === '') {
            $this->addFlash('error', 'Connexion Google non configurée : ajoutez GOOGLE_CLIENT_ID et GOOGLE_CLIENT_SECRET dans .env (voir docs/GOOGLE_OAUTH_SETUP.md).');
            return $this->redirectToRoute('app_login');
        }

        return $this->clientRegistry
            ->getClient('google')
            ->redirect(['email', 'profile'], []);
    }

    /**
     * Callback après autorisation Google : trouve ou crée l'utilisateur puis le connecte.
     */
    #[Route('/google/check', name: 'connect_google_check', methods: ['GET'])]
    public function connectGoogleCheck(Request $request): Response
    {
        $client = $this->clientRegistry->getClient('google');

        try {
            /** @var GoogleUser $googleUser */
            $googleUser = $client->fetchUser();
        } catch (\Exception $e) {
            $this->addFlash('error', 'La connexion avec Google a échoué. Veuillez réessayer.');
            return $this->redirectToRoute('app_login');
        }

        $email = $googleUser->getEmail();
        if (!$email) {
            $this->addFlash('error', 'Google n\'a pas fourni d\'email. Utilisez la connexion classique.');
            return $this->redirectToRoute('app_login');
        }

        $user = $this->userRepository->findOneBy(['googleId' => $googleUser->getId()])
            ?? $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $user = $this->createUserFromGoogle($googleUser);
        } else {
            if (!$user->getGoogleId()) {
                $user->setGoogleId($googleUser->getId());
                if ($googleUser->getAvatar()) {
                    $user->setPhotoUrl($googleUser->getAvatar());
                }
                $this->entityManager->flush();
            }
        }

        $this->entityManager->refresh($user);

        // Connexion via l'authenticator dédié pour que la session soit correctement enregistrée
        return $this->userAuthenticator->authenticateUser(
            $user,
            $this->googleConnectAuthenticator,
            $request
        );
    }

    private function createUserFromGoogle(GoogleUser $googleUser): User
    {
        $user = new User();
        $user->setEmail($googleUser->getEmail());
        $user->setGoogleId($googleUser->getId());
        $user->setPassword(null);
        $user->setRoles([UserRole::USER->value]);
        $user->setStatus(UserStatus::ACTIVE->value);

        $firstName = $googleUser->getFirstName() ?? '';
        $lastName = $googleUser->getLastName() ?? '';
        if ($firstName === '' && $lastName === '') {
            $name = trim($googleUser->getName() ?: 'Utilisateur');
            $parts = explode(' ', $name, 2);
            $firstName = $parts[0] ?? 'Utilisateur';
            $lastName = $parts[1] ?? '';
        }
        $user->setFirstName($firstName !== '' ? $firstName : 'Utilisateur');
        $user->setLastName($lastName !== '' ? $lastName : '—');

        if ($googleUser->getAvatar()) {
            $user->setPhotoUrl($googleUser->getAvatar());
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
