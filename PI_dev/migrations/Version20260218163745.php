<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218163745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coaching_request ALTER priority DROP DEFAULT');
        $this->addSql("ALTER TABLE post ADD status VARCHAR(20) DEFAULT 'published'");
        $this->addSql("UPDATE post SET status = 'published' WHERE status IS NULL");
        $this->addSql("ALTER TABLE post ALTER COLUMN status SET NOT NULL");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coaching_request ALTER priority SET DEFAULT \'normal\'');
        $this->addSql('ALTER TABLE post DROP status');
    }
}
