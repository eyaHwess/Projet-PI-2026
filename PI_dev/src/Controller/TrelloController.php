<?php

namespace App\Controller;

use App\Service\TrelloService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Goal;

class TrelloController extends AbstractController
{
    #[Route('/test-trello', name: 'test_trello')]
    public function test(TrelloService $trelloService): Response
    {
        // TODO: if you want this dynamic, you can pass the board ID as a query param
        $boardId = "655a44217f0176cbc8a0c4f0";

        // Get all lists on the board
        $lists = $trelloService->getListsFromBoard($boardId);

        // Find the "Done" list (case-insensitive)
        $doneListId = null;
        foreach ($lists as $list) {
            if (str_contains(strtolower($list['name']), 'done')) {
                $doneListId = $list['id'];
                break;
            }
        }

        if (!$doneListId) {
            return $this->json([
                'error' => 'Done list not found',
            ], 404);
        }

        // Get cards directly from the Done list so the count always reflects Trello
        $doneCards = $trelloService->getCardsFromList($doneListId);

        return $this->json([
            'done_count'   => \count($doneCards),
            'done_list_id' => $doneListId,
        ]);
    }

    #[Route('/sync-goal/{goalId}', name: 'sync_goal', methods: ['GET', 'POST'])]
    public function syncGoal(
        int $goalId,
        TrelloService $trelloService,
        EntityManagerInterface $entityManager
    ): Response {
        // Retrieve the Goal by ID
        $goal = $entityManager->getRepository(Goal::class)->find($goalId);

        if (!$goal) {
            return $this->json([
                'error' => 'Goal not found'
            ], 404);
        }

        // Check if the goal has a Trello board ID
        $boardId = $goal->getTrelloBoardId();
        if (!$boardId) {
            return $this->json([
                'error' => 'Goal does not have a Trello board ID configured'
            ], 400);
        }

        // Get all lists from the board
        $lists = $trelloService->getListsFromBoard($boardId);

        // Find the "Done" list (case-insensitive)
        $doneListId = null;
        $doneListName = null;
        foreach ($lists as $list) {
            if (str_contains(strtolower($list['name']), 'done')) {
                $doneListId = $list['id'];
                $doneListName = $list['name'];
                break;
            }
        }

        if (!$doneListId) {
            return $this->json([
                'error' => 'Done list not found on the Trello board'
            ], 404);
        }

        // Get cards directly from the Done list (more efficient and accurate)
        $doneCards = $trelloService->getCardsFromList($doneListId);
        $doneCount = count($doneCards);

        // Update the Goal based on done count and required tasks
        $requiredTasks = $goal->getRequiredTasks();
        
        if ($requiredTasks && $requiredTasks > 0) {
            $progress = round(($doneCount / $requiredTasks) * 100);

            if ($doneCount >= $requiredTasks) {
                $goal->setStatus('completed');
                $goal->setProgress(100);
            } else {
                $goal->setStatus('active');
                $goal->setProgress($progress);
            }
        } else {
            $goal->setProgress($doneCount);
        }

        // Persist changes
        $entityManager->flush();

        return $this->json([
            'done_count' => $doneCount,
            'goal_status' => $goal->getStatus(),
            'goal_progress' => $goal->getProgress(),
            'done_list_name' => $doneListName,
            'required_tasks' => $requiredTasks
        ]);
    }
}