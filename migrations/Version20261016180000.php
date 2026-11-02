<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Contenus personnalisés : un cours, un parcours ou un QCM peut être réservé à certains comptes.
 * `reserve` dit si le contenu est réservé ; les tables acces_* listent ses destinataires.
 * Les contenus existants restent ouverts à tous.
 */
final class Version20261016180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cours, parcours et QCM réservés à certains comptes (reserve, acces_cours, acces_parcours, acces_qcm)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE acces_cours (page_content_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_26585FCC8F409273 (page_content_id), INDEX IDX_26585FCCA76ED395 (user_id), PRIMARY KEY(page_content_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acces_parcours (category_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_4119943C12469DE2 (category_id), INDEX IDX_4119943CA76ED395 (user_id), PRIMARY KEY(category_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acces_qcm (qcm_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_14EE4C8DFF6241A6 (qcm_id), INDEX IDX_14EE4C8DA76ED395 (user_id), PRIMARY KEY(qcm_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE acces_cours ADD CONSTRAINT FK_26585FCC8F409273 FOREIGN KEY (page_content_id) REFERENCES appy_PageContent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acces_cours ADD CONSTRAINT FK_26585FCCA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acces_parcours ADD CONSTRAINT FK_4119943C12469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acces_parcours ADD CONSTRAINT FK_4119943CA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acces_qcm ADD CONSTRAINT FK_14EE4C8DFF6241A6 FOREIGN KEY (qcm_id) REFERENCES appy_QCM (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acces_qcm ADD CONSTRAINT FK_14EE4C8DA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_PageContent ADD reserve TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE appy_Category ADD reserve TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE appy_QCM ADD reserve TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_QCM DROP reserve');
        $this->addSql('ALTER TABLE appy_Category DROP reserve');
        $this->addSql('ALTER TABLE appy_PageContent DROP reserve');
        $this->addSql('DROP TABLE acces_qcm');
        $this->addSql('DROP TABLE acces_parcours');
        $this->addSql('DROP TABLE acces_cours');
    }
}
