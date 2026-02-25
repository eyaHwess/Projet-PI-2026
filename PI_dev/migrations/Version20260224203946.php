<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260224203946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message ADD is_toxic BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE message ADD is_spam BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE message ADD moderation_status VARCHAR(20) DEFAULT \'approved\' NOT NULL');
        $this->addSql('ALTER TABLE message ADD toxicity_score DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD spam_score DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD moderation_reason TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message DROP is_toxic');
        $this->addSql('ALTER TABLE message DROP is_spam');
        $this->addSql('ALTER TABLE message DROP moderation_status');
        $this->addSql('ALTER TABLE message DROP toxicity_score');
        $this->addSql('ALTER TABLE message DROP spam_score');
        $this->addSql('ALTER TABLE message DROP moderation_reason');
    }
}
