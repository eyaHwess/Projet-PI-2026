<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225052523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD archetype_short_bio TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD archetype_name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD archetype_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD archetype_data JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD onboarding_answers JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD is_onboarded BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP archetype_short_bio');
        $this->addSql('ALTER TABLE "user" DROP archetype_name');
        $this->addSql('ALTER TABLE "user" DROP archetype_description');
        $this->addSql('ALTER TABLE "user" DROP archetype_data');
        $this->addSql('ALTER TABLE "user" DROP onboarding_answers');
        $this->addSql('ALTER TABLE "user" DROP is_onboarded');
    }
}
