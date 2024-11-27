<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241127050600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mission (id INT AUTO_INCREMENT NOT NULL, super_hero_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, difficulty INT NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', time_limit INT NOT NULL, status VARCHAR(50) NOT NULL, result LONGTEXT DEFAULT NULL, score INT DEFAULT NULL, INDEX IDX_9067F23CB62BE361 (super_hero_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mission_power (mission_id INT NOT NULL, power_id INT NOT NULL, INDEX IDX_3B1C5E9DBE6CAE90 (mission_id), INDEX IDX_3B1C5E9DAB4FC384 (power_id), PRIMARY KEY(mission_id, power_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mission ADD CONSTRAINT FK_9067F23CB62BE361 FOREIGN KEY (super_hero_id) REFERENCES super_hero (id)');
        $this->addSql('ALTER TABLE mission_power ADD CONSTRAINT FK_3B1C5E9DBE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mission_power ADD CONSTRAINT FK_3B1C5E9DAB4FC384 FOREIGN KEY (power_id) REFERENCES power (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission DROP FOREIGN KEY FK_9067F23CB62BE361');
        $this->addSql('ALTER TABLE mission_power DROP FOREIGN KEY FK_3B1C5E9DBE6CAE90');
        $this->addSql('ALTER TABLE mission_power DROP FOREIGN KEY FK_3B1C5E9DAB4FC384');
        $this->addSql('DROP TABLE mission');
        $this->addSql('DROP TABLE mission_power');
    }
}
