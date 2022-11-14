<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221111162708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE about ADD id_categories_id INT NOT NULL');
        $this->addSql('ALTER TABLE about ADD CONSTRAINT FK_B5F422E31C3AC5D2 FOREIGN KEY (id_categories_id) REFERENCES categories (id)');
        $this->addSql('CREATE INDEX IDX_B5F422E31C3AC5D2 ON about (id_categories_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE about DROP FOREIGN KEY FK_B5F422E31C3AC5D2');
        $this->addSql('DROP INDEX IDX_B5F422E31C3AC5D2 ON about');
        $this->addSql('ALTER TABLE about DROP id_categories_id');
    }
}
