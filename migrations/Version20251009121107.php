<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251009121107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appy_ChoicesQCM (id INT AUTO_INCREMENT NOT NULL, qcm_id INT NOT NULL, question VARCHAR(255) NOT NULL, isCorrect TINYINT(1) NOT NULL, explication LONGTEXT NOT NULL, INDEX IDX_B3840B43FF6241A6 (qcm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_LanguageQCM (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_NiveauQCM (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_QCM (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) DEFAULT NULL, solution LONGTEXT NOT NULL, languageQCM_id INT DEFAULT NULL, niveauQCM_id INT DEFAULT NULL, INDEX IDX_47C25A806C5D2C67 (languageQCM_id), INDEX IDX_47C25A80F662DA50 (niveauQCM_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appy_ChoicesQCM ADD CONSTRAINT FK_B3840B43FF6241A6 FOREIGN KEY (qcm_id) REFERENCES appy_QCM (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_QCM ADD CONSTRAINT FK_47C25A806C5D2C67 FOREIGN KEY (languageQCM_id) REFERENCES appy_LanguageQCM (id)');
        $this->addSql('ALTER TABLE appy_QCM ADD CONSTRAINT FK_47C25A80F662DA50 FOREIGN KEY (niveauQCM_id) REFERENCES appy_NiveauQCM (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appy_ChoicesQCM DROP FOREIGN KEY FK_B3840B43FF6241A6');
        $this->addSql('ALTER TABLE appy_QCM DROP FOREIGN KEY FK_47C25A806C5D2C67');
        $this->addSql('ALTER TABLE appy_QCM DROP FOREIGN KEY FK_47C25A80F662DA50');
        $this->addSql('DROP TABLE appy_ChoicesQCM');
        $this->addSql('DROP TABLE appy_LanguageQCM');
        $this->addSql('DROP TABLE appy_NiveauQCM');
        $this->addSql('DROP TABLE appy_QCM');
    }
}
