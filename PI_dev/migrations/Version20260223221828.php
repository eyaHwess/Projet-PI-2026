<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223221828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        
        // First add slug as nullable
        $this->addSql('ALTER TABLE post ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        
        // Generate slugs for existing posts
        $this->addSql("
            UPDATE post 
            SET slug = LOWER(REGEXP_REPLACE(
                REGEXP_REPLACE(title, '[^a-zA-Z0-9\\s-]', '', 'g'),
                '\\s+', '-', 'g'
            )) || '-' || id
            WHERE slug IS NULL
        ");
        
        // Now make slug NOT NULL
        $this->addSql('ALTER TABLE post ALTER COLUMN slug SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A8A6C8D989D9B62 ON post (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_5A8A6C8D989D9B62');
        $this->addSql('ALTER TABLE post DROP slug');
        $this->addSql('ALTER TABLE post DROP deleted_at');
    }
}
