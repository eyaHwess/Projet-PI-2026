<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218214432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE goal_participation ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE message ADD image_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD image_size INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE goal_participation ALTER status SET DEFAULT \'APPROVED\'');
        $this->addSql('ALTER TABLE message DROP image_name');
        $this->addSql('ALTER TABLE message DROP image_size');
        $this->addSql('ALTER TABLE message DROP updated_at');
    }
}
