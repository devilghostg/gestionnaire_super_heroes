<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241128101514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mission_assignment (id INT AUTO_INCREMENT NOT NULL, mission_id INT NOT NULL, hero_id INT NOT NULL, is_active TINYINT(1) NOT NULL, assigned_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', energy DOUBLE PRECISION NOT NULL, INDEX IDX_72C56921BE6CAE90 (mission_id), INDEX IDX_72C5692145B0BCD (hero_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mission_assignment ADD CONSTRAINT FK_72C56921BE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id)');
        $this->addSql('ALTER TABLE mission_assignment ADD CONSTRAINT FK_72C5692145B0BCD FOREIGN KEY (hero_id) REFERENCES super_hero (id)');
        $this->addSql('ALTER TABLE super_hero DROP FOREIGN KEY FK_BD3D67EF5718C47A');
        $this->addSql('ALTER TABLE super_hero ADD CONSTRAINT FK_BD3D67EF5718C47A FOREIGN KEY (last_mission_id) REFERENCES mission (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission_assignment DROP FOREIGN KEY FK_72C56921BE6CAE90');
        $this->addSql('ALTER TABLE mission_assignment DROP FOREIGN KEY FK_72C5692145B0BCD');
        $this->addSql('DROP TABLE mission_assignment');
        $this->addSql('ALTER TABLE super_hero DROP FOREIGN KEY FK_BD3D67EF5718C47A');
        $this->addSql('ALTER TABLE super_hero ADD CONSTRAINT FK_BD3D67EF5718C47A FOREIGN KEY (last_mission_id) REFERENCES mission (id)');
    }
}
