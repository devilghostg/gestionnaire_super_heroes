<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241122095444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE superhero_team (super_hero_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_B5191E12B62BE361 (super_hero_id), INDEX IDX_B5191E12296CD8AE (team_id), PRIMARY KEY(super_hero_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE superhero_team ADD CONSTRAINT FK_B5191E12B62BE361 FOREIGN KEY (super_hero_id) REFERENCES super_hero (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE superhero_team ADD CONSTRAINT FK_B5191E12296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE super_hero_team DROP FOREIGN KEY FK_E868EF86296CD8AE');
        $this->addSql('ALTER TABLE super_hero_team DROP FOREIGN KEY FK_E868EF86B62BE361');
        $this->addSql('DROP TABLE super_hero_team');
        $this->addSql('ALTER TABLE team CHANGE description description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE super_hero_team (super_hero_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_E868EF86B62BE361 (super_hero_id), INDEX IDX_E868EF86296CD8AE (team_id), PRIMARY KEY(super_hero_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE super_hero_team ADD CONSTRAINT FK_E868EF86296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE super_hero_team ADD CONSTRAINT FK_E868EF86B62BE361 FOREIGN KEY (super_hero_id) REFERENCES super_hero (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE superhero_team DROP FOREIGN KEY FK_B5191E12B62BE361');
        $this->addSql('ALTER TABLE superhero_team DROP FOREIGN KEY FK_B5191E12296CD8AE');
        $this->addSql('DROP TABLE superhero_team');
        $this->addSql('ALTER TABLE team CHANGE description description VARCHAR(255) NOT NULL');
    }
}
