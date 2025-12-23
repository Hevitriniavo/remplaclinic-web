<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314111047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE request_reason (id INT AUTO_INCREMENT NOT NULL, request_id INT NOT NULL, reason VARCHAR(255) DEFAULT NULL, reason_value LONGTEXT DEFAULT NULL, INDEX IDX_70E14BF7427EB8A5 (request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE request_reason ADD CONSTRAINT FK_70E14BF7427EB8A5 FOREIGN KEY (request_id) REFERENCES request (id)');
        $this->addSql('ALTER TABLE request DROP raison, DROP raison_value');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_reason DROP FOREIGN KEY FK_70E14BF7427EB8A5');
        $this->addSql('DROP TABLE request_reason');
        $this->addSql('ALTER TABLE request ADD raison VARCHAR(255) DEFAULT NULL, ADD raison_value VARCHAR(255) DEFAULT NULL');
    }
}
