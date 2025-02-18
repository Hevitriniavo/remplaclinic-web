<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250217093423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, address_id INT DEFAULT NULL, speciality_id INT DEFAULT NULL, mobility_id INT DEFAULT NULL, establishment_id INT DEFAULT NULL, clinic_id INT DEFAULT NULL, ordinary_number VARCHAR(20) DEFAULT NULL, civility VARCHAR(10) NOT NULL, surname VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, year_of_birth INT DEFAULT NULL, nationality VARCHAR(20) DEFAULT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, status TINYINT(1) NOT NULL, telephone VARCHAR(50) DEFAULT NULL, telephone2 VARCHAR(50) DEFAULT NULL, fax VARCHAR(50) DEFAULT NULL, position VARCHAR(255) DEFAULT NULL, organism VARCHAR(255) DEFAULT NULL, year_of_alternance INT DEFAULT NULL, current_speciality SMALLINT DEFAULT NULL, comment LONGTEXT DEFAULT NULL, create_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F5B7AF75 (address_id), INDEX IDX_8D93D6493B5A08D7 (speciality_id), INDEX IDX_8D93D6498D92EAA4 (mobility_id), UNIQUE INDEX UNIQ_8D93D6498565851 (establishment_id), INDEX IDX_8D93D649CC22AD4 (clinic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_speciality (user_id INT NOT NULL, speciality_id INT NOT NULL, INDEX IDX_54B06662A76ED395 (user_id), INDEX IDX_54B066623B5A08D7 (speciality_id), PRIMARY KEY(user_id, speciality_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_user_role (user_id INT NOT NULL, user_role_id INT NOT NULL, INDEX IDX_2D084B47A76ED395 (user_id), INDEX IDX_2D084B478E0E3CA6 (user_role_id), PRIMARY KEY(user_id, user_role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_address (id INT AUTO_INCREMENT NOT NULL, country VARCHAR(10) DEFAULT NULL, locality VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, thoroughfare VARCHAR(255) DEFAULT NULL, premise VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_establishment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, beds_count INT DEFAULT NULL, site_web VARCHAR(255) DEFAULT NULL, consultation_count INT DEFAULT NULL, per VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(40) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F5B7AF75 FOREIGN KEY (address_id) REFERENCES user_address (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6493B5A08D7 FOREIGN KEY (speciality_id) REFERENCES speciality (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498D92EAA4 FOREIGN KEY (mobility_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498565851 FOREIGN KEY (establishment_id) REFERENCES user_establishment (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649CC22AD4 FOREIGN KEY (clinic_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_speciality ADD CONSTRAINT FK_54B06662A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_speciality ADD CONSTRAINT FK_54B066623B5A08D7 FOREIGN KEY (speciality_id) REFERENCES speciality (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_user_role ADD CONSTRAINT FK_2D084B47A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_user_role ADD CONSTRAINT FK_2D084B478E0E3CA6 FOREIGN KEY (user_role_id) REFERENCES user_role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F5B7AF75');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493B5A08D7');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498D92EAA4');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498565851');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649CC22AD4');
        $this->addSql('ALTER TABLE user_speciality DROP FOREIGN KEY FK_54B06662A76ED395');
        $this->addSql('ALTER TABLE user_speciality DROP FOREIGN KEY FK_54B066623B5A08D7');
        $this->addSql('ALTER TABLE user_user_role DROP FOREIGN KEY FK_2D084B47A76ED395');
        $this->addSql('ALTER TABLE user_user_role DROP FOREIGN KEY FK_2D084B478E0E3CA6');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_speciality');
        $this->addSql('DROP TABLE user_user_role');
        $this->addSql('DROP TABLE user_address');
        $this->addSql('DROP TABLE user_establishment');
        $this->addSql('DROP TABLE user_role');
    }
}
