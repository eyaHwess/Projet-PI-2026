<?php

namespace App\Controller;

use App\Entity\FcmToken;
use App\Repository\FcmTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/fcm')]
class FcmTokenController extends AbstractController
{
    #[Route('/register', name: 'fcm_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        FcmTokenRepository $fcmTokenRepository
    ): JsonResponse {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;
        $device = $data['device'] ?? 'web';

        if (!$token) {
            return new JsonResponse(['error' => 'Token manquant'], 400);
        }

        // Check if token already exists
        $existingToken = $fcmTokenRepository->findByToken($token);
        
        if ($existingToken) {
            // Update last used date
            $existingToken->updateLastUsed();
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Token mis à jour',
                'tokenId' => $existingToken->getId()
            ]);
        }

        // Create new token
        $fcmToken = new FcmToken();
        $fcmToken->setUser($user);
        $fcmToken->setToken($token);
        $fcmToken->setDevice($device);

        $em->persist($fcmToken);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Token enregistré',
            'tokenId' => $fcmToken->getId()
        ]);
    }

    #[Route('/unregister', name: 'fcm_unregister', methods: ['POST'])]
    public function unregister(
        Request $request,
        EntityManagerInterface $em,
        FcmTokenRepository $fcmTokenRepository
    ): JsonResponse {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;

        if (!$token) {
            return new JsonResponse(['error' => 'Token manquant'], 400);
        }

        $fcmToken = $fcmTokenRepository->findByToken($token);
        
        if (!$fcmToken) {
            return new JsonResponse(['error' => 'Token introuvable'], 404);
        }

        // Verify ownership
        if ($fcmToken->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $em->remove($fcmToken);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Token supprimé'
        ]);
    }

    #[Route('/tokens', name: 'fcm_tokens', methods: ['GET'])]
    public function getTokens(FcmTokenRepository $fcmTokenRepository): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
        }

        $tokens = $fcmTokenRepository->findByUser($user);

        $tokensData = array_map(function ($fcmToken) {
            return [
                'id' => $fcmToken->getId(),
                'device' => $fcmToken->getDevice(),
                'createdAt' => $fcmToken->getCreatedAt()->format('Y-m-d H:i:s'),
                'lastUsedAt' => $fcmToken->getLastUsedAt()->format('Y-m-d H:i:s'),
            ];
        }, $tokens);

        return new JsonResponse([
            'tokens' => $tokensData,
            'count' => count($tokensData)
        ]);
    }
}
