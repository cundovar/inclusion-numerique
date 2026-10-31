<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Connecteur MCP de rédaction et assistant apprenant, repris du gabarit de formation.
 * Uniquement des ajouts : aucune table ni colonne existante n'est supprimée.
 */
final class Version20261030224418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Connecteurs MCP : OAuth, brouillons de QCM, diaporamas, contenus réservés, passages de recherche, cycle éditorial des cours et fiches des captures';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE acces_parcours (category_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_4119943C12469DE2 (category_id), INDEX IDX_4119943CA76ED395 (user_id), PRIMARY KEY(category_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acces_cours (page_content_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_26585FCC8F409273 (page_content_id), INDEX IDX_26585FCCA76ED395 (user_id), PRIMARY KEY(page_content_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acces_qcm (qcm_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_14EE4C8DFF6241A6 (qcm_id), INDEX IDX_14EE4C8DA76ED395 (user_id), PRIMARY KEY(qcm_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE diaporama (id INT AUTO_INCREMENT NOT NULL, cours_id INT DEFAULT NULL, chapitre_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, sous_titre VARCHAR(255) NOT NULL, diapositives JSON NOT NULL, status VARCHAR(20) NOT NULL, author VARCHAR(100) NOT NULL, empreinte_source VARCHAR(64) DEFAULT NULL, review_report JSON DEFAULT NULL, reviewed_by VARCHAR(100) DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_776658BE7ECF78B0 (cours_id), INDEX IDX_776658BE1FBEEF7B (chapitre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_authorization_code (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT NOT NULL, code_hash VARCHAR(64) NOT NULL, redirect_uri VARCHAR(500) NOT NULL, code_challenge VARCHAR(128) NOT NULL, resource VARCHAR(500) NOT NULL, scope VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_793B0817E7530879 (code_hash), INDEX IDX_793B081719EB6921 (client_id), INDEX IDX_793B0817A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_client (id INT AUTO_INCREMENT NOT NULL, client_id VARCHAR(64) NOT NULL, name VARCHAR(60) NOT NULL, redirect_uris JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_AD73274D19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_grant (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT NOT NULL, authorization_code_id INT DEFAULT NULL, resource VARCHAR(500) NOT NULL, scope VARCHAR(64) NOT NULL, access_token_hash VARCHAR(64) NOT NULL, access_expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', refresh_token_hash VARCHAR(64) NOT NULL, refresh_expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', revoked_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_4E068C5D9982CF5B (access_token_hash), UNIQUE INDEX UNIQ_4E068C5D7288F9EC (refresh_token_hash), INDEX IDX_4E068C5D19EB6921 (client_id), INDEX IDX_4E068C5DA76ED395 (user_id), INDEX IDX_4E068C5D847B7245 (authorization_code_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE qcm_draft (id INT AUTO_INCREMENT NOT NULL, theme_id INT DEFAULT NULL, level_id INT DEFAULT NULL, published_qcm_id INT DEFAULT NULL, question VARCHAR(255) NOT NULL, explanation LONGTEXT NOT NULL, answers JSON NOT NULL, status VARCHAR(20) NOT NULL, author VARCHAR(100) NOT NULL, review_report JSON DEFAULT NULL, reviewed_by VARCHAR(100) DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C85292EE59027487 (theme_id), INDEX IDX_C85292EE5FB14BA7 (level_id), INDEX IDX_C85292EE369F8154 (published_qcm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rag_passage (id INT AUTO_INCREMENT NOT NULL, page_content_id INT NOT NULL, position INT NOT NULL, content LONGTEXT NOT NULL, checksum VARCHAR(64) NOT NULL, INDEX IDX_RAG_PASSAGE_COURSE (page_content_id), FULLTEXT INDEX rag_passage_content_fulltext (content), UNIQUE INDEX uniq_rag_course_position (page_content_id, position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE acces_parcours ADD CONSTRAINT FK_4119943C12469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acces_parcours ADD CONSTRAINT FK_4119943CA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acces_cours ADD CONSTRAINT FK_26585FCC8F409273 FOREIGN KEY (page_content_id) REFERENCES appy_PageContent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acces_cours ADD CONSTRAINT FK_26585FCCA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acces_qcm ADD CONSTRAINT FK_14EE4C8DFF6241A6 FOREIGN KEY (qcm_id) REFERENCES appy_QCM (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acces_qcm ADD CONSTRAINT FK_14EE4C8DA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diaporama ADD CONSTRAINT FK_776658BE7ECF78B0 FOREIGN KEY (cours_id) REFERENCES appy_PageContent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diaporama ADD CONSTRAINT FK_776658BE1FBEEF7B FOREIGN KEY (chapitre_id) REFERENCES appy_Menus (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_authorization_code ADD CONSTRAINT FK_793B081719EB6921 FOREIGN KEY (client_id) REFERENCES oauth_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_authorization_code ADD CONSTRAINT FK_793B0817A76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_grant ADD CONSTRAINT FK_4E068C5D19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_grant ADD CONSTRAINT FK_4E068C5DA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_grant ADD CONSTRAINT FK_4E068C5D847B7245 FOREIGN KEY (authorization_code_id) REFERENCES oauth_authorization_code (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE qcm_draft ADD CONSTRAINT FK_C85292EE59027487 FOREIGN KEY (theme_id) REFERENCES appy_LanguageQCM (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE qcm_draft ADD CONSTRAINT FK_C85292EE5FB14BA7 FOREIGN KEY (level_id) REFERENCES appy_NiveauQCM (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE qcm_draft ADD CONSTRAINT FK_C85292EE369F8154 FOREIGN KEY (published_qcm_id) REFERENCES appy_QCM (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rag_passage ADD CONSTRAINT FK_F1A1688E8F409273 FOREIGN KEY (page_content_id) REFERENCES appy_PageContent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_Category ADD illustration_id INT DEFAULT NULL, ADD ordre INT DEFAULT NULL, ADD reserve TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE appy_Category ADD CONSTRAINT FK_3BD612555926566C FOREIGN KEY (illustration_id) REFERENCES course_media (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3BD612555926566C ON appy_Category (illustration_id)');
        $this->addSql('ALTER TABLE appy_PageContent ADD status VARCHAR(20) DEFAULT \'publie\' NOT NULL, ADD source_url VARCHAR(500) DEFAULT NULL, ADD author VARCHAR(100) DEFAULT NULL, ADD verified_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', ADD review_report JSON DEFAULT NULL, ADD reviewed_by VARCHAR(100) DEFAULT NULL, ADD reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD ordre INT DEFAULT NULL, ADD reserve TINYINT(1) DEFAULT 0 NOT NULL');
        // Un cours masqué dans le back-office est un brouillon pour le cycle éditorial ; les autres restent publiés
        $this->addSql("UPDATE appy_PageContent SET status = 'brouillon' WHERE visible = 0");
        $this->addSql('ALTER TABLE appy_QCM ADD reserve TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE appy_SuperMenu ADD ordre INT DEFAULT NULL');
        $this->addSql('ALTER TABLE course_media ADD kind VARCHAR(20) DEFAULT \'capture\' NOT NULL, ADD service VARCHAR(100) DEFAULT NULL, ADD source_url VARCHAR(500) DEFAULT NULL, ADD captured_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', ADD author VARCHAR(100) DEFAULT NULL, ADD next_review_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE filename filename VARCHAR(255) DEFAULT NULL, CHANGE mime_type mime_type VARCHAR(100) DEFAULT NULL, CHANGE width width INT DEFAULT NULL, CHANGE height height INT DEFAULT NULL, CHANGE checksum checksum VARCHAR(64) DEFAULT NULL');
        // Les images existantes viennent des agents de génération : des illustrations, sans page officielle à citer
        $this->addSql("UPDATE course_media SET kind = 'illustration', updated_at = created_at, caption = COALESCE(caption, '')");
        $this->addSql('ALTER TABLE course_media CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE caption caption LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE acces_parcours DROP FOREIGN KEY FK_4119943C12469DE2');
        $this->addSql('ALTER TABLE acces_parcours DROP FOREIGN KEY FK_4119943CA76ED395');
        $this->addSql('ALTER TABLE acces_cours DROP FOREIGN KEY FK_26585FCC8F409273');
        $this->addSql('ALTER TABLE acces_cours DROP FOREIGN KEY FK_26585FCCA76ED395');
        $this->addSql('ALTER TABLE acces_qcm DROP FOREIGN KEY FK_14EE4C8DFF6241A6');
        $this->addSql('ALTER TABLE acces_qcm DROP FOREIGN KEY FK_14EE4C8DA76ED395');
        $this->addSql('ALTER TABLE diaporama DROP FOREIGN KEY FK_776658BE7ECF78B0');
        $this->addSql('ALTER TABLE diaporama DROP FOREIGN KEY FK_776658BE1FBEEF7B');
        $this->addSql('ALTER TABLE oauth_authorization_code DROP FOREIGN KEY FK_793B081719EB6921');
        $this->addSql('ALTER TABLE oauth_authorization_code DROP FOREIGN KEY FK_793B0817A76ED395');
        $this->addSql('ALTER TABLE oauth_grant DROP FOREIGN KEY FK_4E068C5D19EB6921');
        $this->addSql('ALTER TABLE oauth_grant DROP FOREIGN KEY FK_4E068C5DA76ED395');
        $this->addSql('ALTER TABLE oauth_grant DROP FOREIGN KEY FK_4E068C5D847B7245');
        $this->addSql('ALTER TABLE qcm_draft DROP FOREIGN KEY FK_C85292EE59027487');
        $this->addSql('ALTER TABLE qcm_draft DROP FOREIGN KEY FK_C85292EE5FB14BA7');
        $this->addSql('ALTER TABLE qcm_draft DROP FOREIGN KEY FK_C85292EE369F8154');
        $this->addSql('ALTER TABLE rag_passage DROP FOREIGN KEY FK_F1A1688E8F409273');
        $this->addSql('DROP TABLE acces_parcours');
        $this->addSql('DROP TABLE acces_cours');
        $this->addSql('DROP TABLE acces_qcm');
        $this->addSql('DROP TABLE diaporama');
        $this->addSql('DROP TABLE oauth_authorization_code');
        $this->addSql('DROP TABLE oauth_client');
        $this->addSql('DROP TABLE oauth_grant');
        $this->addSql('DROP TABLE qcm_draft');
        $this->addSql('DROP TABLE rag_passage');
        $this->addSql('ALTER TABLE appy_Category DROP FOREIGN KEY FK_3BD612555926566C');
        $this->addSql('DROP INDEX IDX_3BD612555926566C ON appy_Category');
        $this->addSql('ALTER TABLE appy_Category DROP illustration_id, DROP ordre, DROP reserve');
        $this->addSql('ALTER TABLE appy_PageContent DROP status, DROP source_url, DROP author, DROP verified_at, DROP review_report, DROP reviewed_by, DROP reviewed_at, DROP ordre, DROP reserve');
        $this->addSql('ALTER TABLE appy_QCM DROP reserve');
        $this->addSql('ALTER TABLE appy_SuperMenu DROP ordre');
        $this->addSql('ALTER TABLE course_media DROP kind, DROP service, DROP source_url, DROP captured_at, DROP author, DROP next_review_at, DROP updated_at, CHANGE caption caption LONGTEXT DEFAULT NULL, CHANGE filename filename VARCHAR(255) NOT NULL, CHANGE mime_type mime_type VARCHAR(100) NOT NULL, CHANGE width width INT NOT NULL, CHANGE height height INT NOT NULL, CHANGE checksum checksum VARCHAR(64) NOT NULL');
    }
}
