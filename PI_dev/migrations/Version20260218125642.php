<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218125642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise à jour des valeurs de priorité: standard -> normal';
    }

    public function up(Schema $schema): void
    {
        // Mettre à jour toutes les valeurs 'standard' en 'normal'
        $this->addSql("UPDATE coaching_request SET priority = 'normal' WHERE priority = 'standard'");
        
        // Modifier la valeur par défaut de la colonne
        $this->addSql("ALTER TABLE coaching_request ALTER priority SET DEFAULT 'normal'");
    }

    public function down(Schema $schema): void
    {
        // Revenir à 'standard'
        $this->addSql("UPDATE coaching_request SET priority = 'standard' WHERE priority = 'normal'");
        $this->addSql("ALTER TABLE coaching_request ALTER priority SET DEFAULT 'standard'");
    }
}
