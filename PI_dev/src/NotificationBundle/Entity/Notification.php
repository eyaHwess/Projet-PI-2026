<?php

namespace App\NotificationBundle\Entity;

/**
 * Compatibility alias for legacy bundle code.
 *
 * IMPORTANT: This class is intentionally NOT a Doctrine entity.
 * The application uses `App\Entity\Notification` as the single mapped entity for the `notifications` table.
 */
class Notification extends \App\Entity\Notification
{
}
