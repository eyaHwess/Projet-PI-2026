<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211165953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Add as nullable first to allow backfill on existing rows
        $this->addSql('ALTER TABLE goal ADD chatroom_id INT DEFAULT NULL');

        // Ensure each existing goal has a chatroom row (chatroom schema: id, chatroom, no, goal_id)
        $this->addSql(<<<'SQL'
            INSERT INTO chatroom (chatroom, no, goal_id)
            SELECT CURRENT_TIMESTAMP, ('goal-' || g.id::text), g.id
            FROM goal g
            LEFT JOIN chatroom c ON c.goal_id = g.id
            WHERE c.id IS NULL
        SQL);

        // Backfill goal.chatroom_id based on chatroom.goal_id
        $this->addSql(<<<'SQL'
            UPDATE goal g
            SET chatroom_id = (
                SELECT MIN(c.id) FROM chatroom c WHERE c.goal_id = g.id
            )
            WHERE g.chatroom_id IS NULL
        SQL);

        // Now enforce the constraint (data is filled)
        $this->addSql('ALTER TABLE goal ALTER COLUMN chatroom_id SET NOT NULL');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT FK_FCDCEB2ECAF8A031 FOREIGN KEY (chatroom_id) REFERENCES chatroom (id) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FCDCEB2ECAF8A031 ON goal (chatroom_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE goal DROP CONSTRAINT FK_FCDCEB2ECAF8A031');
        $this->addSql('DROP INDEX UNIQ_FCDCEB2ECAF8A031');
        $this->addSql('ALTER TABLE goal DROP chatroom_id');
    }
}
