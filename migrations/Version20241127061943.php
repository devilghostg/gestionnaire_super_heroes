<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241127061943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE super_hero_power (super_hero_id INT NOT NULL, power_id INT NOT NULL, INDEX IDX_2275A209B62BE361 (super_hero_id), INDEX IDX_2275A209AB4FC384 (power_id), PRIMARY KEY(super_hero_id, power_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE super_hero_power ADD CONSTRAINT FK_2275A209B62BE361 FOREIGN KEY (super_hero_id) REFERENCES super_hero (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE super_hero_power ADD CONSTRAINT FK_2275A209AB4FC384 FOREIGN KEY (power_id) REFERENCES power (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE super_hero_power DROP FOREIGN KEY FK_2275A209B62BE361');
        $this->addSql('ALTER TABLE super_hero_power DROP FOREIGN KEY FK_2275A209AB4FC384');
        $this->addSql('DROP TABLE super_hero_power');
    }
}
