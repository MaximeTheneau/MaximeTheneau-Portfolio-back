<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241115114602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE posts_relations (posts_source INT NOT NULL, posts_target INT NOT NULL, INDEX IDX_F60E0AB4358858DA (posts_source), INDEX IDX_F60E0AB42C6D0855 (posts_target), PRIMARY KEY(posts_source, posts_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE posts_relations ADD CONSTRAINT FK_F60E0AB4358858DA FOREIGN KEY (posts_source) REFERENCES posts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE posts_relations ADD CONSTRAINT FK_F60E0AB42C6D0855 FOREIGN KEY (posts_target) REFERENCES posts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_relations DROP FOREIGN KEY FK_B502C18F2C6D0855');
        $this->addSql('ALTER TABLE post_relations DROP FOREIGN KEY FK_B502C18F358858DA');
        $this->addSql('DROP TABLE post_relations');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_relations (posts_source INT NOT NULL, posts_target INT NOT NULL, INDEX IDX_B502C18F358858DA (posts_source), INDEX IDX_B502C18F2C6D0855 (posts_target), PRIMARY KEY(posts_source, posts_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE post_relations ADD CONSTRAINT FK_B502C18F2C6D0855 FOREIGN KEY (posts_target) REFERENCES posts (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_relations ADD CONSTRAINT FK_B502C18F358858DA FOREIGN KEY (posts_source) REFERENCES posts (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE posts_relations DROP FOREIGN KEY FK_F60E0AB4358858DA');
        $this->addSql('ALTER TABLE posts_relations DROP FOREIGN KEY FK_F60E0AB42C6D0855');
        $this->addSql('DROP TABLE posts_relations');
    }
}
