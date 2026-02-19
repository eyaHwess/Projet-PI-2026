<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215213355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coaching_request ADD goal VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE coaching_request ADD level VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE coaching_request ADD frequency VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE coaching_request ADD budget DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE coaching_request ADD coaching_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD review_count INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD price_per_session DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD bio VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD photo_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD badges JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD responds_quickly BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD total_sessions INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coaching_request DROP goal');
        $this->addSql('ALTER TABLE coaching_request DROP level');
        $this->addSql('ALTER TABLE coaching_request DROP frequency');
        $this->addSql('ALTER TABLE coaching_request DROP budget');
        $this->addSql('ALTER TABLE coaching_request DROP coaching_type');
        $this->addSql('ALTER TABLE "user" DROP review_count');
        $this->addSql('ALTER TABLE "user" DROP price_per_session');
        $this->addSql('ALTER TABLE "user" DROP bio');
        $this->addSql('ALTER TABLE "user" DROP photo_url');
        $this->addSql('ALTER TABLE "user" DROP badges');
        $this->addSql('ALTER TABLE "user" DROP responds_quickly');
        $this->addSql('ALTER TABLE "user" DROP total_sessions');
    }
}
