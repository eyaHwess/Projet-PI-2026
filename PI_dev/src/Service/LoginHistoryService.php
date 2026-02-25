<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserLoginHistory;
use App\Repository\UserLoginHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginHistoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserLoginHistoryRepository $loginHistoryRepository,
        private EmailService $emailService,
        private RequestStack $requestStack,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Enregistre une nouvelle connexion et détecte si elle est suspecte
     */
    public function recordLogin(User $user): void
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (!$request) {
            $this->logger->warning('Impossible d\'enregistrer la connexion : pas de requête');
            return;
        }

        $ipAddress = $request->getClientIp() ?? 'unknown';
        $userAgent = $request->headers->get('User-Agent') ?? 'unknown';

        // Vérifier si c'est une nouvelle IP
        $isNewIp = !$this->loginHistoryRepository->hasIpBeenUsed($user, $ipAddress);

        $loginHistory = new UserLoginHistory();
        $loginHistory->setUser($user);
        $loginHistory->setIpAddress($ipAddress);
        $loginHistory->setUserAgent($userAgent);
        $loginHistory->setIsSuspicious($isNewIp);

        try {
            $this->entityManager->persist($loginHistory);
            $this->entityManager->flush();

            $this->logger->info('Connexion enregistrée', [
                'user_id' => $user->getId(),
                'ip' => $ipAddress,
                'suspicious' => $isNewIp
            ]);

            // Envoyer un email si connexion suspecte
            if ($isNewIp) {
                $this->emailService->sendSuspiciousLogin(
                    $user->getEmail(),
                    $user->getFirstName(),
                    $ipAddress,
                    $userAgent,
                    $loginHistory->getLoggedAt()
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'enregistrement de la connexion', [
                'error' => $e->getMessage(),
                'user_id' => $user->getId()
            ]);
        }
    }

    /**
     * Récupère les dernières connexions d'un utilisateur
     */
    public function getRecentLogins(User $user, int $limit = 5): array
    {
        return $this->loginHistoryRepository->findRecentByUser($user, $limit);
    }

    /**
     * Récupère les connexions suspectes d'un utilisateur
     */
    public function getSuspiciousLogins(User $user): array
    {
        return $this->loginHistoryRepository->findSuspiciousByUser($user);
    }

    /**
     * Compte les connexions suspectes non lues
     */
    public function countSuspiciousLogins(User $user): int
    {
        return $this->loginHistoryRepository->countUnreadSuspicious($user);
    }

    /**
     * Nettoie les anciennes entrées
     */
    public function cleanOldEntries(): int
    {
        return $this->loginHistoryRepository->cleanOldEntries();
    }
}
