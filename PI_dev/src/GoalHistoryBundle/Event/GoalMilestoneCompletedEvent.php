<?php

namespace App\GoalHistoryBundle\Event;

use App\Entity\Goal;
use App\Entity\User;

/**
 * Dispatched when a milestone inside a Goal is marked as completed.
 */
final class GoalMilestoneCompletedEvent extends AbstractGoalEvent
{
    public const NAME = 'goal_history.milestone_completed';

    public function __construct(
        Goal             $goal,
        private string   $milestoneName,
        ?User            $user = null,
    ) {
        parent::__construct($goal, $user);
    }

    public function getMilestoneName(): string
    {
        return $this->milestoneName;
    }
}
