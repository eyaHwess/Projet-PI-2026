<?php

namespace App\GoalHistoryBundle\Event;

use App\Entity\Goal;
use App\Entity\User;

/**
 * Dispatched after a Goal's fields are updated.
 *
 * Pass $changedFields as an associative array of:
 *   ['field_name' => ['old' => $old, 'new' => $new], ...]
 */
final class GoalUpdatedEvent extends AbstractGoalEvent
{
    public const NAME = 'goal_history.goal_updated';

    public function __construct(
        Goal            $goal,
        ?User           $user          = null,
        private array   $changedFields = [],
    ) {
        parent::__construct($goal, $user);
    }

    public function getChangedFields(): array
    {
        return $this->changedFields;
    }
}
