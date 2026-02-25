<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260218150000_add_session_priority extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne priority (Haute / Moyenne / Faible) à la table session.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "session" ADD priority VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "session" DROP priority');
    }
}
