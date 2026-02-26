<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222165910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chatroom ALTER state DROP DEFAULT');
        $this->addSql('ALTER TABLE message_reaction DROP CONSTRAINT fk_adf1c3e6a76ed395');
        $this->addSql('ALTER TABLE message_reaction DROP CONSTRAINT fk_adf1c3e6537a1329');
        $this->addSql('ALTER TABLE message_reaction ALTER reaction_type TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE message_reaction ADD CONSTRAINT FK_ADF1C3E6A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE message_reaction ADD CONSTRAINT FK_ADF1C3E6537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER INDEX unique_reaction RENAME TO unique_user_message_reaction');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chatroom ALTER state SET DEFAULT \'active\'');
        $this->addSql('ALTER TABLE message_reaction DROP CONSTRAINT FK_ADF1C3E6537A1329');
        $this->addSql('ALTER TABLE message_reaction DROP CONSTRAINT FK_ADF1C3E6A76ED395');
        $this->addSql('ALTER TABLE message_reaction ALTER reaction_type TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE message_reaction ADD CONSTRAINT fk_adf1c3e6537a1329 FOREIGN KEY (message_id) REFERENCES message (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message_reaction ADD CONSTRAINT fk_adf1c3e6a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX unique_user_message_reaction RENAME TO unique_reaction');
    }
}
