<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305120000_add_notification_goal_id extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add goal_id to notifications for goal_invitation link';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notifications ADD goal_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notifications DROP goal_id');
    }
}
