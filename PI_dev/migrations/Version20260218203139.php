<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218203139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD images JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE post ALTER status TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE post ALTER status SET DEFAULT \'active\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP images');
        $this->addSql('ALTER TABLE post ALTER status TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE post ALTER status SET DEFAULT \'published\'');
    }
}
