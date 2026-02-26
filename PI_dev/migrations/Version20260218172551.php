<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218172551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE goal_participation ADD status VARCHAR(20) DEFAULT \'APPROVED\' NOT NULL');
        $this->addSql('UPDATE goal_participation SET status = \'APPROVED\' WHERE status IS NULL');
        $this->addSql('ALTER TABLE goal_participation ALTER role DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE goal_participation DROP status');
        $this->addSql('ALTER TABLE goal_participation ALTER role SET DEFAULT \'MEMBER\'');
    }
}
