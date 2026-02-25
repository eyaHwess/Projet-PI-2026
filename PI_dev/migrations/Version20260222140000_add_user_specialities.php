<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260222140000_add_user_specialities extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add specialities (JSON) to user table for AI compatibility';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD specialities JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP specialities');
    }
}
