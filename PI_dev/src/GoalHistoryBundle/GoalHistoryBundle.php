<?php

namespace App\GoalHistoryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GoalHistoryBundle
 *
 * Tracks and logs all important actions related to Goals.
 * Completely decoupled from controllers — log via service or Symfony events.
 *
 * Usage:
 *   $logger->log($goal, 'status_changed', 'draft', 'active');
 *   // or dispatch a GoalStatusChangedEvent, GoalCreatedEvent, etc.
 */
class GoalHistoryBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__) . '/GoalHistoryBundle';
    }
}
