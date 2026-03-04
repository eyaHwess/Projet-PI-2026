<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add photoPath field to reclamation table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // This migration may run before the reclamation table exists in some histories.
        // Make it safe: only apply if the table exists, and only add the column if missing.
        $this->addSql(<<<'SQL'
            DO $$
            BEGIN
                IF to_regclass('public.reclamation') IS NOT NULL THEN
                    ALTER TABLE reclamation ADD COLUMN IF NOT EXISTS photo_path VARCHAR(255) DEFAULT NULL;
                END IF;
            END
            $$;
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DO $$
            BEGIN
                IF to_regclass('public.reclamation') IS NOT NULL THEN
                    ALTER TABLE reclamation DROP COLUMN IF EXISTS photo_path;
                END IF;
            END
            $$;
        SQL);
    }
}
