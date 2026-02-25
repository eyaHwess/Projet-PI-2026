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
        $this->addSql('ALTER TABLE goal ADD chatroom_id INT NOT NULL');
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
