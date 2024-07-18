<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240718131824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE posts_subtopic (posts_id INT NOT NULL, subtopic_id INT NOT NULL, INDEX IDX_3D3CE908D5E258C5 (posts_id), INDEX IDX_3D3CE90814C59DB4 (subtopic_id), PRIMARY KEY(posts_id, subtopic_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE subtopic (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(70) NOT NULL, slug VARCHAR(70) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE posts_subtopic ADD CONSTRAINT FK_3D3CE908D5E258C5 FOREIGN KEY (posts_id) REFERENCES posts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE posts_subtopic ADD CONSTRAINT FK_3D3CE90814C59DB4 FOREIGN KEY (subtopic_id) REFERENCES subtopic (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A727ACA70');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AD5E258C5');
        $this->addSql('ALTER TABLE keyword_posts DROP FOREIGN KEY FK_66B48D5B115D4552');
        $this->addSql('ALTER TABLE keyword_posts DROP FOREIGN KEY FK_66B48D5BD5E258C5');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE keyword_posts');
        $this->addSql('DROP TABLE keyword');
        $this->addSql('ALTER TABLE list_posts DROP link, DROP link_subtitle');
        $this->addSql('ALTER TABLE paragraph_posts DROP slug, DROP link, DROP link_subtitle, DROP img_post_paragh_file, DROP img_width, DROP img_height, DROP img_post, DROP srcset');
        $this->addSql('ALTER TABLE posts ADD video VARCHAR(500) DEFAULT NULL, ADD github VARCHAR(500) DEFAULT NULL, ADD website VARCHAR(500) DEFAULT NULL, DROP formatted_date, DROP contents_html, DROP draft, DROP img_width, DROP img_height, DROP srcset, CHANGE meta_description meta_description VARCHAR(160) NOT NULL, CHANGE heading heading VARCHAR(65) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comments (id INT AUTO_INCREMENT NOT NULL, posts_id INT DEFAULT NULL, user VARCHAR(70) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, comment VARCHAR(2000) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, accepted TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, parent_id INT DEFAULT NULL, INDEX IDX_5F9E962A727ACA70 (parent_id), INDEX IDX_5F9E962AD5E258C5 (posts_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE keyword_posts (keyword_id INT NOT NULL, posts_id INT NOT NULL, INDEX IDX_66B48D5BD5E258C5 (posts_id), INDEX IDX_66B48D5B115D4552 (keyword_id), PRIMARY KEY(keyword_id, posts_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE keyword (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A727ACA70 FOREIGN KEY (parent_id) REFERENCES comments (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AD5E258C5 FOREIGN KEY (posts_id) REFERENCES posts (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE keyword_posts ADD CONSTRAINT FK_66B48D5B115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE keyword_posts ADD CONSTRAINT FK_66B48D5BD5E258C5 FOREIGN KEY (posts_id) REFERENCES posts (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE posts_subtopic DROP FOREIGN KEY FK_3D3CE908D5E258C5');
        $this->addSql('ALTER TABLE posts_subtopic DROP FOREIGN KEY FK_3D3CE90814C59DB4');
        $this->addSql('DROP TABLE posts_subtopic');
        $this->addSql('DROP TABLE subtopic');
        $this->addSql('ALTER TABLE paragraph_posts ADD slug VARCHAR(50) DEFAULT NULL, ADD link VARCHAR(500) DEFAULT NULL, ADD link_subtitle VARCHAR(255) DEFAULT NULL, ADD img_post_paragh_file VARCHAR(255) DEFAULT NULL, ADD img_width INT DEFAULT NULL, ADD img_height INT DEFAULT NULL, ADD img_post VARCHAR(500) DEFAULT NULL, ADD srcset LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE list_posts ADD link VARCHAR(500) DEFAULT NULL, ADD link_subtitle VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE posts ADD formatted_date VARCHAR(255) NOT NULL, ADD contents_html VARCHAR(5000) NOT NULL, ADD draft TINYINT(1) DEFAULT NULL, ADD img_width INT DEFAULT NULL, ADD img_height INT DEFAULT NULL, ADD srcset LONGTEXT DEFAULT NULL, DROP video, DROP github, DROP website, CHANGE heading heading VARCHAR(70) NOT NULL, CHANGE meta_description meta_description VARCHAR(1000) NOT NULL');
    }
}
