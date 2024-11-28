<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241128093053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission CHANGE completed_at completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE super_hero ADD last_mission_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE super_hero ADD CONSTRAINT FK_BD3D67EF5718C47A FOREIGN KEY (last_mission_id) REFERENCES mission (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD3D67EF5718C47A ON super_hero (last_mission_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission CHANGE completed_at completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE super_hero DROP FOREIGN KEY FK_BD3D67EF5718C47A');
        $this->addSql('DROP INDEX UNIQ_BD3D67EF5718C47A ON super_hero');
        $this->addSql('ALTER TABLE super_hero DROP last_mission_id');
    }
}
