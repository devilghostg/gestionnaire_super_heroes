<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241129070942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission DROP FOREIGN KEY FK_9067F23CB62BE361');
        $this->addSql('DROP INDEX IDX_9067F23CB62BE361 ON mission');
        $this->addSql('ALTER TABLE mission DROP super_hero_id, DROP reward');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission ADD super_hero_id INT DEFAULT NULL, ADD reward INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mission ADD CONSTRAINT FK_9067F23CB62BE361 FOREIGN KEY (super_hero_id) REFERENCES super_hero (id)');
        $this->addSql('CREATE INDEX IDX_9067F23CB62BE361 ON mission (super_hero_id)');
    }
}
