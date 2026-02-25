<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260222120000_add_session_price_payment extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add price and paymentStatus to session table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "session" ADD price DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE "session" ADD payment_status VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "session" DROP price');
        $this->addSql('ALTER TABLE "session" DROP payment_status');
    }
}
