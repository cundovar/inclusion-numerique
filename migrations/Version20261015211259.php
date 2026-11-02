<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Brouillons de QCM de l'assistant de rédaction : hors de l'API publique jusqu'à leur publication,
 * qui crée le QCM et ses réponses dans les tables publiques.
 */
final class Version20261015211259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Assistant de rédaction : brouillons de QCM (qcm_draft)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE qcm_draft (id INT AUTO_INCREMENT NOT NULL, theme_id INT DEFAULT NULL, level_id INT DEFAULT NULL, published_qcm_id INT DEFAULT NULL, question VARCHAR(255) NOT NULL, explanation LONGTEXT NOT NULL, answers JSON NOT NULL, status VARCHAR(20) NOT NULL, author VARCHAR(100) NOT NULL, review_report JSON DEFAULT NULL, reviewed_by VARCHAR(100) DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C85292EE59027487 (theme_id), INDEX IDX_C85292EE5FB14BA7 (level_id), INDEX IDX_C85292EE369F8154 (published_qcm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE qcm_draft ADD CONSTRAINT FK_C85292EE59027487 FOREIGN KEY (theme_id) REFERENCES appy_LanguageQCM (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE qcm_draft ADD CONSTRAINT FK_C85292EE5FB14BA7 FOREIGN KEY (level_id) REFERENCES appy_NiveauQCM (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE qcm_draft ADD CONSTRAINT FK_C85292EE369F8154 FOREIGN KEY (published_qcm_id) REFERENCES appy_QCM (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE qcm_draft DROP FOREIGN KEY FK_C85292EE59027487');
        $this->addSql('ALTER TABLE qcm_draft DROP FOREIGN KEY FK_C85292EE5FB14BA7');
        $this->addSql('ALTER TABLE qcm_draft DROP FOREIGN KEY FK_C85292EE369F8154');
        $this->addSql('DROP TABLE qcm_draft');
    }
}
