<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219132116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_subscription (id INT AUTO_INCREMENT NOT NULL, end_at DATETIME DEFAULT NULL, status TINYINT(1) DEFAULT NULL, end_notification TINYINT(1) DEFAULT NULL, installation_count INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498D92EAA4');
        $this->addSql('DROP INDEX IDX_8D93D6498D92EAA4 ON user');
        $this->addSql('ALTER TABLE user CHANGE telephone2 telephone2 VARCHAR(255) DEFAULT NULL, CHANGE mobility_id subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499A1887DC FOREIGN KEY (subscription_id) REFERENCES user_subscription (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6499A1887DC ON user (subscription_id)');
        $this->addSql('ALTER TABLE user_establishment ADD service_name VARCHAR(255) DEFAULT NULL, ADD chief_service_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499A1887DC');
        $this->addSql('DROP TABLE user_subscription');
        $this->addSql('DROP INDEX UNIQ_8D93D6499A1887DC ON user');
        $this->addSql('ALTER TABLE user CHANGE telephone2 telephone2 VARCHAR(50) DEFAULT NULL, CHANGE subscription_id mobility_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498D92EAA4 FOREIGN KEY (mobility_id) REFERENCES region (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8D93D6498D92EAA4 ON user (mobility_id)');
        $this->addSql('ALTER TABLE user_establishment DROP service_name, DROP chief_service_name');
    }
}
