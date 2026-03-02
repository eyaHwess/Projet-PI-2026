<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Notification;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Checks activities whose reminder time has come and creates notifications.
 */
class ActivityReminderService
{
    public function __construct(
        private ActivityRepository     $activityRepository,
        private EntityManagerInterface $entityManager,
        private NotificationService    $notificationService,
    ) {
    }

    /**
     * Process all pending reminders whose reminderAt <= now.
     * Returns the number of notifications sent.
     */
    public function processPendingReminders(): int
    {
        $now        = new \DateTime();
        $activities = $this->activityRepository->findPendingReminders($now);
        $count      = 0;

        foreach ($activities as $activity) {
            $routine = $activity->getRoutine();
            if (!$routine) {
                continue;
            }

            $goal = $routine->getGoal();
            if (!$goal) {
                continue;
            }

            $user = $goal->getUser();
            if (!$user) {
                continue;
            }

            // Build a clear message
            $startLabel = $activity->getStartTime()
                ? $activity->getStartTime()->format('H:i')
                : '—';

            $message = sprintf(
                '⏰ Rappel : "%s" commence à %s (routine "%s")',
                $activity->getTitle(),
                $startLabel,
                $routine->getTitle()
            );

            // Create notification
            $this->notificationService->createAndPublish(
                $user,
                'activity_reminder',
                $message,
                null
            );

            // Mark reminder as processed so it is not sent again
            $activity->setHasReminder(false);
            $activity->setReminderAt(null);

            $count++;
        }

        if ($count > 0) {
            $this->entityManager->flush();
        }

        return $count;
    }
}
