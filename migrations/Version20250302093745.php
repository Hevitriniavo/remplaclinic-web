<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302093745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE request (id INT AUTO_INCREMENT NOT NULL, applicant_id INT NOT NULL, region_id INT DEFAULT NULL, speciality_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, status SMALLINT NOT NULL, started_at DATETIME DEFAULT NULL, show_end_at TINYINT(1) DEFAULT NULL, end_at DATETIME DEFAULT NULL, last_sent_at DATETIME NOT NULL, request_type VARCHAR(255) DEFAULT NULL, remuneration VARCHAR(255) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, position_count INT DEFAULT NULL, accomodation_included SMALLINT DEFAULT NULL, transport_cost_refunded SMALLINT DEFAULT NULL, retrocession VARCHAR(255) NOT NULL, replacement_type VARCHAR(255) DEFAULT NULL, raison VARCHAR(255) DEFAULT NULL, raison_value VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, INDEX IDX_3B978F9F97139001 (applicant_id), INDEX IDX_3B978F9F98260155 (region_id), INDEX IDX_3B978F9F3B5A08D7 (speciality_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request_speciality (request_id INT NOT NULL, speciality_id INT NOT NULL, INDEX IDX_BB2E6734427EB8A5 (request_id), INDEX IDX_BB2E67343B5A08D7 (speciality_id), PRIMARY KEY(request_id, speciality_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request_history (id INT AUTO_INCREMENT NOT NULL, request_id INT NOT NULL, sent_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D9E021C427EB8A5 (request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request_response (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, request_id INT DEFAULT NULL, status SMALLINT DEFAULT NULL, create_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_CB5EEBDEA76ED395 (user_id), INDEX IDX_CB5EEBDE427EB8A5 (request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F97139001 FOREIGN KEY (applicant_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F98260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F3B5A08D7 FOREIGN KEY (speciality_id) REFERENCES speciality (id)');
        $this->addSql('ALTER TABLE request_speciality ADD CONSTRAINT FK_BB2E6734427EB8A5 FOREIGN KEY (request_id) REFERENCES request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE request_speciality ADD CONSTRAINT FK_BB2E67343B5A08D7 FOREIGN KEY (speciality_id) REFERENCES speciality (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE request_history ADD CONSTRAINT FK_D9E021C427EB8A5 FOREIGN KEY (request_id) REFERENCES request (id)');
        $this->addSql('ALTER TABLE request_response ADD CONSTRAINT FK_CB5EEBDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE request_response ADD CONSTRAINT FK_CB5EEBDE427EB8A5 FOREIGN KEY (request_id) REFERENCES request (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F97139001');
        $this->addSql('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F98260155');
        $this->addSql('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F3B5A08D7');
        $this->addSql('ALTER TABLE request_speciality DROP FOREIGN KEY FK_BB2E6734427EB8A5');
        $this->addSql('ALTER TABLE request_speciality DROP FOREIGN KEY FK_BB2E67343B5A08D7');
        $this->addSql('ALTER TABLE request_history DROP FOREIGN KEY FK_D9E021C427EB8A5');
        $this->addSql('ALTER TABLE request_response DROP FOREIGN KEY FK_CB5EEBDEA76ED395');
        $this->addSql('ALTER TABLE request_response DROP FOREIGN KEY FK_CB5EEBDE427EB8A5');
        $this->addSql('DROP TABLE request');
        $this->addSql('DROP TABLE request_speciality');
        $this->addSql('DROP TABLE request_history');
        $this->addSql('DROP TABLE request_response');
    }
}
