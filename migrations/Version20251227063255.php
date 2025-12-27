<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251227063255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE ordinary_number ordinary_number VARCHAR(255) DEFAULT NULL, CHANGE civility civility VARCHAR(255) NOT NULL, CHANGE year_of_birth year_of_birth VARCHAR(255) DEFAULT NULL, CHANGE nationality nationality VARCHAR(255) DEFAULT NULL, CHANGE year_of_alternance year_of_alternance VARCHAR(255) DEFAULT NULL, CHANGE current_speciality current_speciality INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_address CHANGE locality locality VARCHAR(255) DEFAULT NULL, CHANGE postal_code postal_code VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE ordinary_number ordinary_number VARCHAR(20) DEFAULT NULL, CHANGE civility civility VARCHAR(10) NOT NULL, CHANGE year_of_birth year_of_birth INT DEFAULT NULL, CHANGE nationality nationality VARCHAR(20) DEFAULT NULL, CHANGE year_of_alternance year_of_alternance INT DEFAULT NULL, CHANGE current_speciality current_speciality SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_address CHANGE locality locality VARCHAR(100) DEFAULT NULL, CHANGE postal_code postal_code VARCHAR(20) DEFAULT NULL');
    }
}
