<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304181500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Safe forward sync: add session.objective column if missing.';
    }

    public function up(Schema $schema): void
    {
        // Some databases are missing this column even though the entity expects it.
        $this->addSql(<<<'SQL'
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM information_schema.columns
                    WHERE table_schema = 'public' AND table_name = 'session' AND column_name = 'objective'
                ) THEN
                    ALTER TABLE session ADD COLUMN objective TEXT DEFAULT NULL;
                END IF;
            END
            $$;
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session DROP COLUMN IF EXISTS objective');
    }
}

