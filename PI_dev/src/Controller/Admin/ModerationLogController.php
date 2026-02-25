<?php

namespace App\Controller\Admin;

use App\Service\Moderation\ModerationLogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/moderation', name: 'admin_moderation_')]
class ModerationLogController extends AbstractController
{
    #[Route('/logs', name: 'logs', methods: ['GET'])]
    public function logs(Request $request, ModerationLogService $moderationLogService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $limit = (int) $request->query->get('limit', 200);
        $limit = max(20, min(1000, $limit));

        $violations = $moderationLogService->getViolations($limit);

        return $this->render('admin/moderation/logs.html.twig', [
            'violations' => $violations,
        ]);
    }

    #[Route('/logs/{id}/review', name: 'review', methods: ['POST'])]
    public function markReviewed(string $id, ModerationLogService $moderationLogService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();
        $adminId = method_exists($user, 'getId') ? (int) $user->getId() : 0;
        $adminEmail = method_exists($user, 'getEmail') ? (string) $user->getEmail() : null;

        if ($adminId <= 0) {
            return new JsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $moderationLogService->markReviewed($id, $adminId, $adminEmail);

        return new JsonResponse(['success' => true]);
    }
}

