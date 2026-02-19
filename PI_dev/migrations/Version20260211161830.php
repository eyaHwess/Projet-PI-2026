<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211161830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chatroom DROP CONSTRAINT fk_1f3e6ec4667d1afe');
        $this->addSql('DROP INDEX uniq_1f3e6ec4667d1afe');
        $this->addSql('ALTER TABLE chatroom DROP goalid');
        $this->addSql('ALTER TABLE chatroom DROP title');
        $this->addSql('ALTER TABLE chatroom DROP goal_id');
        $this->addSql('ALTER TABLE chatroom RENAME COLUMN idcreated_at TO created_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chatroom ADD goalid VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE chatroom ADD title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE chatroom ADD goal_id INT NOT NULL');
        $this->addSql('ALTER TABLE chatroom RENAME COLUMN created_at TO idcreated_at');
        $this->addSql('ALTER TABLE chatroom ADD CONSTRAINT fk_1f3e6ec4667d1afe FOREIGN KEY (goal_id) REFERENCES goal (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_1f3e6ec4667d1afe ON chatroom (goal_id)');
    }
}
