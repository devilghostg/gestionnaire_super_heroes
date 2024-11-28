<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241128121245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mission_history_super_hero (mission_history_id INT NOT NULL, super_hero_id INT NOT NULL, INDEX IDX_D1C563A5C05A9C9 (mission_history_id), INDEX IDX_D1C563AB62BE361 (super_hero_id), PRIMARY KEY(mission_history_id, super_hero_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mission_history_super_hero ADD CONSTRAINT FK_D1C563A5C05A9C9 FOREIGN KEY (mission_history_id) REFERENCES mission_history (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mission_history_super_hero ADD CONSTRAINT FK_D1C563AB62BE361 FOREIGN KEY (super_hero_id) REFERENCES super_hero (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission_history_super_hero DROP FOREIGN KEY FK_D1C563A5C05A9C9');
        $this->addSql('ALTER TABLE mission_history_super_hero DROP FOREIGN KEY FK_D1C563AB62BE361');
        $this->addSql('DROP TABLE mission_history_super_hero');
    }
}
