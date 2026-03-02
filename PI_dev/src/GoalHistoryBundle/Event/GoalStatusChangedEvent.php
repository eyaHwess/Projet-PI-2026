<?php

namespace App\GoalHistoryBundle\Event;

use App\Entity\Goal;
use App\Entity\User;

/**
 * Dispatched when a Goal's status transitions from one value to another.
 *
 * Example:
 *   $this->eventDispatcher->dispatch(
 *       new GoalStatusChangedEvent($goal, 'draft', 'active', $user)
 *   );
 */
final class GoalStatusChangedEvent extends AbstractGoalEvent
{
    public const NAME = 'goal_history.status_changed';

    public function __construct(
        Goal             $goal,
        private string   $oldStatus,
        private string   $newStatus,
        ?User            $user = null,
    ) {
        parent::__construct($goal, $user);
    }

    public function getOldStatus(): string
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }
}
