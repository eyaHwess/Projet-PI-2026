<?php

namespace App\GoalHistoryBundle\Event;

use App\Entity\Goal;
use App\Entity\User;

/**
 * Dispatched BEFORE a Goal is removed so the history entry can still
 * reference the entity before Doctrine deletes it.
 */
final class GoalDeletedEvent extends AbstractGoalEvent
{
    public const NAME = 'goal_history.goal_deleted';

    public function __construct(Goal $goal, ?User $user = null)
    {
        parent::__construct($goal, $user);
    }
}
