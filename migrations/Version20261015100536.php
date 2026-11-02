<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Statut éditorial des cours (à la place de « visible ») et fiche complète des captures.
 * Les données existantes sont conservées : un cours visible devient « publié », un cours masqué « brouillon ».
 */
final class Version20261015100536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Statuts éditoriaux des cours, source et vérification ; métadonnées des captures';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_PageContent ADD status VARCHAR(20) DEFAULT \'brouillon\' NOT NULL, ADD source_url VARCHAR(500) DEFAULT NULL, ADD verified_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('UPDATE appy_PageContent SET status = CASE WHEN visible = 0 THEN \'brouillon\' ELSE \'publie\' END');
        $this->addSql('ALTER TABLE appy_PageContent DROP visible');

        $this->addSql('ALTER TABLE course_media DROP FOREIGN KEY FK_8387A6E4591CC992');
        $this->addSql('DROP INDEX IDX_8387A6E4591CC992 ON course_media');
        $this->addSql('ALTER TABLE course_media ADD kind VARCHAR(20) DEFAULT \'capture\' NOT NULL, ADD service VARCHAR(100) DEFAULT NULL, ADD source_url VARCHAR(500) DEFAULT NULL, ADD captured_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', ADD author VARCHAR(100) DEFAULT NULL, ADD next_review_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP course_id, DROP prompt');
        $this->addSql('UPDATE course_media SET updated_at = created_at, caption = COALESCE(caption, \'\')');
        $this->addSql('ALTER TABLE course_media CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE filename filename VARCHAR(255) DEFAULT NULL, CHANGE mime_type mime_type VARCHAR(100) DEFAULT NULL, CHANGE width width INT DEFAULT NULL, CHANGE height height INT DEFAULT NULL, CHANGE checksum checksum VARCHAR(64) DEFAULT NULL, CHANGE caption caption LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_PageContent ADD visible TINYINT(1) DEFAULT 1');
        $this->addSql('UPDATE appy_PageContent SET visible = (status = \'publie\')');
        $this->addSql('ALTER TABLE appy_PageContent DROP status, DROP source_url, DROP verified_at');

        $this->addSql('DELETE FROM course_media WHERE filename IS NULL');
        $this->addSql('UPDATE course_media SET mime_type = COALESCE(mime_type, \'\'), width = COALESCE(width, 0), height = COALESCE(height, 0), checksum = COALESCE(checksum, \'\')');
        $this->addSql('ALTER TABLE course_media ADD course_id INT DEFAULT NULL, ADD prompt LONGTEXT DEFAULT NULL, DROP kind, DROP service, DROP source_url, DROP captured_at, DROP author, DROP next_review_at, DROP updated_at, CHANGE caption caption LONGTEXT DEFAULT NULL, CHANGE filename filename VARCHAR(255) NOT NULL, CHANGE mime_type mime_type VARCHAR(100) NOT NULL, CHANGE width width INT NOT NULL, CHANGE height height INT NOT NULL, CHANGE checksum checksum VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE course_media ADD CONSTRAINT FK_8387A6E4591CC992 FOREIGN KEY (course_id) REFERENCES appy_PageContent (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8387A6E4591CC992 ON course_media (course_id)');
    }
}
