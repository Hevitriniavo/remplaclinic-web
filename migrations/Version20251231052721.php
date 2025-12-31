<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251231052721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_importation_script (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, script VARCHAR(255) NOT NULL, options JSON DEFAULT NULL, status INT NOT NULL, last_id VARCHAR(20) DEFAULT NULL, last_count INT DEFAULT NULL, executed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', output LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_scheduler (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, script VARCHAR(255) NOT NULL, options JSON DEFAULT NULL, time VARCHAR(30) NOT NULL, executed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', output LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE app_importation_script');
        $this->addSql('DROP TABLE app_scheduler');
    }
}
