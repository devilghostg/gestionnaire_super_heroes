<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241127043535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team_super_hero DROP FOREIGN KEY FK_83E4152A296CD8AE');
        $this->addSql('ALTER TABLE team_super_hero DROP FOREIGN KEY FK_83E4152AB62BE361');
        $this->addSql('DROP TABLE team_super_hero');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE team_super_hero (team_id INT NOT NULL, super_hero_id INT NOT NULL, INDEX IDX_83E4152AB62BE361 (super_hero_id), INDEX IDX_83E4152A296CD8AE (team_id), PRIMARY KEY(team_id, super_hero_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE team_super_hero ADD CONSTRAINT FK_83E4152A296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_super_hero ADD CONSTRAINT FK_83E4152AB62BE361 FOREIGN KEY (super_hero_id) REFERENCES super_hero (id) ON DELETE CASCADE');
    }
}
