<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * GoalHistoryBundle — creates the goal_history table.
 */
final class Version20260226120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the goal_history table for the GoalHistoryBundle.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE goal_history (
                id           SERIAL       NOT NULL,
                goal_id      INT          NOT NULL,
                user_id      INT          DEFAULT NULL,
                action       VARCHAR(100) NOT NULL,
                old_status   VARCHAR(50)  DEFAULT NULL,
                new_status   VARCHAR(50)  DEFAULT NULL,
                description  TEXT         DEFAULT NULL,
                metadata     JSON         DEFAULT NULL,
                created_at   TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
                PRIMARY KEY (id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_goal_history_goal    ON goal_history (goal_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_goal_history_user    ON goal_history (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_goal_history_action  ON goal_history (action)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_goal_history_created ON goal_history (created_at)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE goal_history
                ADD CONSTRAINT fk_goal_history_goal
                    FOREIGN KEY (goal_id) REFERENCES goal (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goal_history
                ADD CONSTRAINT fk_goal_history_user
                    FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE SET NULL
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN goal_history.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE goal_history DROP CONSTRAINT IF EXISTS fk_goal_history_goal');
        $this->addSql('ALTER TABLE goal_history DROP CONSTRAINT IF EXISTS fk_goal_history_user');
        $this->addSql('DROP TABLE IF EXISTS goal_history');
    }
}
