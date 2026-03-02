<?php

namespace App\GoalHistoryBundle\EventListener;

use App\GoalHistoryBundle\Event\GoalCreatedEvent;
use App\GoalHistoryBundle\Event\GoalDeletedEvent;
use App\GoalHistoryBundle\Event\GoalMilestoneCompletedEvent;
use App\GoalHistoryBundle\Event\GoalStatusChangedEvent;
use App\GoalHistoryBundle\Event\GoalUpdatedEvent;
use App\GoalHistoryBundle\Service\GoalHistoryLogger;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Listens to all goal-related events and delegates to GoalHistoryLogger.
 *
 * Each method is wired via the #[AsEventListener] attribute — no YAML needed.
 */
#[AsEventListener(event: GoalCreatedEvent::class,           method: 'onGoalCreated')]
#[AsEventListener(event: GoalUpdatedEvent::class,           method: 'onGoalUpdated')]
#[AsEventListener(event: GoalStatusChangedEvent::class,     method: 'onStatusChanged')]
#[AsEventListener(event: GoalDeletedEvent::class,           method: 'onGoalDeleted')]
#[AsEventListener(event: GoalMilestoneCompletedEvent::class, method: 'onMilestoneCompleted')]
final class GoalHistoryListener
{
    public function __construct(
        private GoalHistoryLogger $historyLogger,
    ) {
    }

    public function onGoalCreated(GoalCreatedEvent $event): void
    {
        $this->historyLogger->logCreated(
            $event->getGoal(),
            $event->getUser(),
        );
    }

    public function onGoalUpdated(GoalUpdatedEvent $event): void
    {
        $this->historyLogger->logUpdated(
            $event->getGoal(),
            $event->getUser(),
            $event->getChangedFields(),
        );
    }

    public function onStatusChanged(GoalStatusChangedEvent $event): void
    {
        $this->historyLogger->logStatusChanged(
            $event->getGoal(),
            $event->getOldStatus(),
            $event->getNewStatus(),
            $event->getUser(),
        );
    }

    public function onGoalDeleted(GoalDeletedEvent $event): void
    {
        $this->historyLogger->logDeleted(
            $event->getGoal(),
            $event->getUser(),
        );
    }

    public function onMilestoneCompleted(GoalMilestoneCompletedEvent $event): void
    {
        $this->historyLogger->logMilestone(
            $event->getGoal(),
            $event->getMilestoneName(),
            $event->getUser(),
        );
    }
}
