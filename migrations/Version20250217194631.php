<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250217194631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_region (user_id INT NOT NULL, region_id INT NOT NULL, INDEX IDX_6A30EA4BA76ED395 (user_id), INDEX IDX_6A30EA4B98260155 (region_id), PRIMARY KEY(user_id, region_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_region ADD CONSTRAINT FK_6A30EA4BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_region ADD CONSTRAINT FK_6A30EA4B98260155 FOREIGN KEY (region_id) REFERENCES region (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD user_comment LONGTEXT DEFAULT NULL, ADD cv VARCHAR(255) DEFAULT NULL, ADD diplom VARCHAR(255) DEFAULT NULL, ADD licence VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_region DROP FOREIGN KEY FK_6A30EA4BA76ED395');
        $this->addSql('ALTER TABLE user_region DROP FOREIGN KEY FK_6A30EA4B98260155');
        $this->addSql('DROP TABLE user_region');
        $this->addSql('ALTER TABLE user DROP user_comment, DROP cv, DROP diplom, DROP licence');
    }
}
