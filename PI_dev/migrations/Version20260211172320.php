<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211172320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chatroom ADD goal_id INT NOT NULL');
        $this->addSql('ALTER TABLE chatroom ADD CONSTRAINT FK_1F3E6EC4667D1AFE FOREIGN KEY (goal_id) REFERENCES goal (id) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F3E6EC4667D1AFE ON chatroom (goal_id)');
        $this->addSql('ALTER TABLE goal DROP CONSTRAINT fk_fcdceb2ecaf8a031');
        $this->addSql('DROP INDEX uniq_fcdceb2ecaf8a031');
        $this->addSql('ALTER TABLE goal DROP chatroom_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chatroom DROP CONSTRAINT FK_1F3E6EC4667D1AFE');
        $this->addSql('DROP INDEX UNIQ_1F3E6EC4667D1AFE');
        $this->addSql('ALTER TABLE chatroom DROP goal_id');
        $this->addSql('ALTER TABLE goal ADD chatroom_id INT NOT NULL');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT fk_fcdceb2ecaf8a031 FOREIGN KEY (chatroom_id) REFERENCES chatroom (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_fcdceb2ecaf8a031 ON goal (chatroom_id)');
    }
}
