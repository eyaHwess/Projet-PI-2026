<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\MessageReaction;
use App\Repository\MessageReactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/message')]
class MessageReactionController extends AbstractController
{
    #[Route('/{id}/react', name: 'message_react', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function react(
        Message $message,
        Request $request,
        EntityManagerInterface $em,
        MessageReactionRepository $reactionRepository
    ): JsonResponse {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $reactionType = $data['type'] ?? null;

        if (!$reactionType) {
            return new JsonResponse(['error' => 'Type de réaction manquant'], 400);
        }

        // Types de réactions autorisés
        // NB: l'UI du chatroom moderne utilise like/clap/fire/heart.
        // On garde love/wow pour compatibilité avec d'autres pages/tests.
        $allowedTypes = ['like', 'clap', 'fire', 'heart', 'love', 'wow'];
        if (!in_array($reactionType, $allowedTypes)) {
            return new JsonResponse(['error' => 'Type de réaction invalide'], 400);
        }

        // Vérifier si l'utilisateur a déjà réagi avec ce type
        $existingReaction = $reactionRepository->findUserReaction($message, $user, $reactionType);

        $action = null;
        if ($existingReaction) {
            // Supprimer la réaction (toggle)
            $em->remove($existingReaction);
            $em->flush();
            $action = 'removed';
            $hasReacted = false;
        } else {
            // Ajouter la réaction
            $reaction = new MessageReaction();
            $reaction->setMessage($message);
            $reaction->setUser($user);
            $reaction->setReactionType($reactionType);

            $em->persist($reaction);
            $em->flush();
            $action = 'added';
            $hasReacted = true;
        }

        // Recalculer des compteurs fiables (inclut 0 si absent)
        $rawCounts = $reactionRepository->getReactionCounts($message); // e.g. ['like' => 2, ...]
        $counts = [];
        foreach ($allowedTypes as $type) {
            $counts[$type] = (int)($rawCounts[$type] ?? 0);
        }
        $count = $counts[$reactionType] ?? 0;

        return new JsonResponse([
            'success' => true,
            'type' => $reactionType,
            'count' => $count,
            'hasReacted' => $hasReacted,
            'action' => $action,
            'counts' => $counts,
        ]);
    }

    #[Route('/{id}/reactions', name: 'message_reactions', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getReactions(
        Message $message,
        MessageReactionRepository $reactionRepository
    ): JsonResponse {
        $user = $this->getUser();
        
        $counts = $reactionRepository->getReactionCounts($message);
        $userReactions = $user ? $reactionRepository->getUserReactions($message, $user) : [];

        return new JsonResponse([
            'counts' => $counts,
            'userReactions' => $userReactions
        ]);
    }

    #[Route('/{id}/reaction-users/{type}', name: 'message_reaction_users', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getReactionUsers(
        Message $message,
        string $type,
        MessageReactionRepository $reactionRepository
    ): JsonResponse {
        $users = $reactionRepository->getUsersByReactionType($message, $type);

        $usersData = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'fullName' => $user->getFirstName() . ' ' . $user->getLastName()
            ];
        }, $users);

        return new JsonResponse([
            'type' => $type,
            'users' => $usersData,
            'count' => count($usersData)
        ]);
    }
}