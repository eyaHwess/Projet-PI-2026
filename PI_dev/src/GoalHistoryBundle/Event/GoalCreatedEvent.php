<?php

namespace App\GoalHistoryBundle\Event;

use App\Entity\Goal;
use App\Entity\User;

/**
 * Dispatched after a Goal is persisted for the first time.
 *
 * Usage:
 *   $this->eventDispatcher->dispatch(new GoalCreatedEvent($goal, $this->getUser()));
 */
final class GoalCreatedEvent extends AbstractGoalEvent
{
    /** Event name used with the string-based dispatcher (legacy support). */
    public const NAME = 'goal_history.goal_created';

    public function __construct(Goal $goal, ?User $user = null)
    {
        parent::__construct($goal, $user);
    }
}
