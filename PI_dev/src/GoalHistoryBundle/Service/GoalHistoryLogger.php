<?php

namespace App\GoalHistoryBundle\Service;

use App\Entity\Goal;
use App\Entity\User;
use App\GoalHistoryBundle\Entity\GoalHistory;
use App\GoalHistoryBundle\Repository\GoalHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * GoalHistoryLogger
 *
 * The central service for recording Goal history entries.
 * Inject this service anywhere in your application to log actions.
 *
 * Examples:
 *   $logger->log($goal, GoalHistory::ACTION_CREATED, user: $user);
 *   $logger->log($goal, GoalHistory::ACTION_STATUS_CHANGED, 'draft', 'active', $user);
 *   $logger->logMilestone($goal, 'First week completed!', $user);
 */
class GoalHistoryLogger
{
    public function __construct(
        private EntityManagerInterface  $entityManager,
        private GoalHistoryRepository   $repository,
        private ?LoggerInterface        $logger = null,
    ) {
    }

    // ── Core log method ───────────────────────────────────────────────────

    /**
     * Record a history entry for a goal action.
     *
     * @param Goal        $goal        The goal being acted upon
     * @param string      $action      Action constant (use GoalHistory::ACTION_*)
     * @param string|null $oldStatus   Previous status (for status transitions)
     * @param string|null $newStatus   New status (for status transitions)
     * @param User|null   $user        Who performed the action (null = system)
     * @param string|null $description Human-readable context
     * @param array|null  $metadata    Extra structured data (changed fields, etc.)
     */
    public function log(
        Goal    $goal,
        string  $action,
        ?string $oldStatus   = null,
        ?string $newStatus   = null,
        ?User   $user        = null,
        ?string $description = null,
        ?array  $metadata    = null,
    ): GoalHistory {
        $entry = new GoalHistory();
        $entry->setGoal($goal)
              ->setAction($action)
              ->setOldStatus($oldStatus)
              ->setNewStatus($newStatus)
              ->setUser($user)
              ->setDescription($description)
              ->setMetadata($metadata);

        $this->entityManager->persist($entry);
        // Flush délégué à l'appelant pour éviter "Flush In Loop" (ex: GoalController::edit appelle log() plusieurs fois).

        $this->logger?->info('[GoalHistory] {action} on goal #{id}', [
            'action'    => $action,
            'id'        => $goal->getId(),
            'oldStatus' => $oldStatus,
            'newStatus' => $newStatus,
            'user'      => $user?->getEmail(),
        ]);

        return $entry;
    }

    // ── Convenience wrappers ──────────────────────────────────────────────

    /** Log that a goal was just created. */
    public function logCreated(Goal $goal, ?User $user = null): GoalHistory
    {
        return $this->log(
            goal:        $goal,
            action:      GoalHistory::ACTION_CREATED,
            newStatus:   $goal->getStatus(),
            user:        $user,
            description: sprintf('Objectif "%s" créé.', $goal->getTitle()),
        );
    }

    /**
     * Log a field update. Pass an array of changed fields:
     *   ['title' => ['old' => 'Foo', 'new' => 'Bar'], ...]
     */
    public function logUpdated(Goal $goal, ?User $user = null, array $changedFields = []): GoalHistory
    {
        $desc = 'Objectif modifié';
        if ($changedFields) {
            $parts = array_map(
                static fn($field) => $field,
                array_keys($changedFields)
            );
            $desc .= ' : ' . implode(', ', $parts);
        }

        return $this->log(
            goal:        $goal,
            action:      GoalHistory::ACTION_UPDATED,
            user:        $user,
            description: $desc,
            metadata:    $changedFields ?: null,
        );
    }

    /** Log a status transition. */
    public function logStatusChanged(Goal $goal, string $oldStatus, string $newStatus, ?User $user = null): GoalHistory
    {
        return $this->log(
            goal:        $goal,
            action:      GoalHistory::ACTION_STATUS_CHANGED,
            oldStatus:   $oldStatus,
            newStatus:   $newStatus,
            user:        $user,
            description: sprintf('Statut : %s → %s', $oldStatus, $newStatus),
        );
    }

    /** Log a priority change. */
    public function logPriorityChanged(Goal $goal, ?string $oldPriority, ?string $newPriority, ?User $user = null): GoalHistory
    {
        return $this->log(
            goal:        $goal,
            action:      GoalHistory::ACTION_PRIORITY_CHANGED,
            user:        $user,
            description: sprintf('Priorité : %s → %s', $oldPriority ?? 'non définie', $newPriority ?? 'non définie'),
            metadata:    ['old_priority' => $oldPriority, 'new_priority' => $newPriority],
        );
    }

    /** Log a progress update. */
    public function logProgressUpdated(Goal $goal, int $oldProgress, int $newProgress, ?User $user = null): GoalHistory
    {
        return $this->log(
            goal:        $goal,
            action:      GoalHistory::ACTION_PROGRESS_UPDATED,
            user:        $user,
            description: sprintf('Progression : %d%% → %d%%', $oldProgress, $newProgress),
            metadata:    ['old_progress' => $oldProgress, 'new_progress' => $newProgress],
        );
    }

    /** Log that a milestone was completed. */
    public function logMilestone(Goal $goal, string $milestoneName, ?User $user = null): GoalHistory
    {
        return $this->log(
            goal:        $goal,
            action:      GoalHistory::ACTION_MILESTONE_DONE,
            user:        $user,
            description: sprintf('Jalon accompli : "%s"', $milestoneName),
            metadata:    ['milestone' => $milestoneName],
        );
    }

    /** Log goal deletion (call BEFORE removing the entity). */
    public function logDeleted(Goal $goal, ?User $user = null): GoalHistory
    {
        return $this->log(
            goal:        $goal,
            action:      GoalHistory::ACTION_DELETED,
            oldStatus:   $goal->getStatus(),
            user:        $user,
            description: sprintf('Objectif "%s" supprimé.', $goal->getTitle()),
        );
    }

    // ── Query helpers ─────────────────────────────────────────────────────

    /** @return GoalHistory[] */
    public function getHistoryForGoal(Goal $goal, int $limit = 50): array
    {
        return $this->repository->findByGoal($goal, $limit);
    }

    /** @return GoalHistory[] */
    public function getHistoryForUser(User $user, int $limit = 100): array
    {
        return $this->repository->findByUser($user, $limit);
    }
}
