<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228191907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mail_log (id INT AUTO_INCREMENT NOT NULL, sender JSON NOT NULL, target VARCHAR(255) NOT NULL, cc VARCHAR(255) DEFAULT NULL, bcc VARCHAR(255) DEFAULT NULL, html TINYINT(1) NOT NULL, subject VARCHAR(255) NOT NULL, body LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', event VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE mail_log');
    }
}
