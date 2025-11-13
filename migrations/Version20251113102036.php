<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113102036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appy_ChoicesQCM (id INT AUTO_INCREMENT NOT NULL, qcm_id INT NOT NULL, question VARCHAR(255) NOT NULL, isCorrect TINYINT(1) NOT NULL, explication LONGTEXT DEFAULT NULL, INDEX IDX_B3840B43FF6241A6 (qcm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_Exo (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(100) NOT NULL, exoMenu_id INT DEFAULT NULL, INDEX IDX_B53FFFF08F27B68 (exoMenu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_ExoBlock (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT DEFAULT NULL, code LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, exoContent_id INT DEFAULT NULL, INDEX IDX_626E16DAD5B57270 (exoContent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_ExoContent (id INT AUTO_INCREMENT NOT NULL, exo_id INT DEFAULT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(50) DEFAULT NULL, content LONGTEXT DEFAULT NULL, code LONGTEXT DEFAULT NULL, exoMenu_id INT DEFAULT NULL, INDEX IDX_B81F09DFDA1C6F33 (exo_id), INDEX IDX_B81F09DF12469DE2 (category_id), INDEX IDX_B81F09DF8F27B68 (exoMenu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_ExoMenu (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, label VARCHAR(100) DEFAULT NULL, INDEX IDX_61CDAD812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_LanguageQCM (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_NiveauQCM (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_PropositionIA (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(50) NOT NULL, statut VARCHAR(20) NOT NULL, payload JSON DEFAULT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_QCM (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) DEFAULT NULL, solution LONGTEXT NOT NULL, languageQCM_id INT DEFAULT NULL, niveauQCM_id INT DEFAULT NULL, INDEX IDX_47C25A806C5D2C67 (languageQCM_id), INDEX IDX_47C25A80F662DA50 (niveauQCM_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_UserPageVisit (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, page_url VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, visited_at DATETIME NOT NULL, user_agent VARCHAR(255) DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, time_spent INT DEFAULT NULL, INDEX IDX_7D09FA76A76ED395 (user_id), INDEX IDX_7D09FA76A76ED395EDA764E3 (user_id, visited_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appy_ChoicesQCM ADD CONSTRAINT FK_B3840B43FF6241A6 FOREIGN KEY (qcm_id) REFERENCES appy_QCM (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_Exo ADD CONSTRAINT FK_B53FFFF08F27B68 FOREIGN KEY (exoMenu_id) REFERENCES appy_ExoMenu (id)');
        $this->addSql('ALTER TABLE appy_ExoBlock ADD CONSTRAINT FK_626E16DAD5B57270 FOREIGN KEY (exoContent_id) REFERENCES appy_ExoContent (id)');
        $this->addSql('ALTER TABLE appy_ExoContent ADD CONSTRAINT FK_B81F09DFDA1C6F33 FOREIGN KEY (exo_id) REFERENCES appy_Exo (id)');
        $this->addSql('ALTER TABLE appy_ExoContent ADD CONSTRAINT FK_B81F09DF12469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)');
        $this->addSql('ALTER TABLE appy_ExoContent ADD CONSTRAINT FK_B81F09DF8F27B68 FOREIGN KEY (exoMenu_id) REFERENCES appy_ExoMenu (id)');
        $this->addSql('ALTER TABLE appy_ExoMenu ADD CONSTRAINT FK_61CDAD812469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)');
        $this->addSql('ALTER TABLE appy_QCM ADD CONSTRAINT FK_47C25A806C5D2C67 FOREIGN KEY (languageQCM_id) REFERENCES appy_LanguageQCM (id)');
        $this->addSql('ALTER TABLE appy_QCM ADD CONSTRAINT FK_47C25A80F662DA50 FOREIGN KEY (niveauQCM_id) REFERENCES appy_NiveauQCM (id)');
        $this->addSql('ALTER TABLE appy_UserPageVisit ADD CONSTRAINT FK_7D09FA76A76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_E46960F5A76ED395');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_E46960F5C4663E4');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_AC29E71BA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_AC29E71BC4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_favorite RENAME INDEX idx_e46960f5a76ed395 TO IDX_AC29E71BA76ED395');
        $this->addSql('ALTER TABLE appy_favorite RENAME INDEX idx_e46960f5c4663e4 TO IDX_AC29E71BC4663E4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appy_ChoicesQCM DROP FOREIGN KEY FK_B3840B43FF6241A6');
        $this->addSql('ALTER TABLE appy_Exo DROP FOREIGN KEY FK_B53FFFF08F27B68');
        $this->addSql('ALTER TABLE appy_ExoBlock DROP FOREIGN KEY FK_626E16DAD5B57270');
        $this->addSql('ALTER TABLE appy_ExoContent DROP FOREIGN KEY FK_B81F09DFDA1C6F33');
        $this->addSql('ALTER TABLE appy_ExoContent DROP FOREIGN KEY FK_B81F09DF12469DE2');
        $this->addSql('ALTER TABLE appy_ExoContent DROP FOREIGN KEY FK_B81F09DF8F27B68');
        $this->addSql('ALTER TABLE appy_ExoMenu DROP FOREIGN KEY FK_61CDAD812469DE2');
        $this->addSql('ALTER TABLE appy_QCM DROP FOREIGN KEY FK_47C25A806C5D2C67');
        $this->addSql('ALTER TABLE appy_QCM DROP FOREIGN KEY FK_47C25A80F662DA50');
        $this->addSql('ALTER TABLE appy_UserPageVisit DROP FOREIGN KEY FK_7D09FA76A76ED395');
        $this->addSql('DROP TABLE appy_ChoicesQCM');
        $this->addSql('DROP TABLE appy_Exo');
        $this->addSql('DROP TABLE appy_ExoBlock');
        $this->addSql('DROP TABLE appy_ExoContent');
        $this->addSql('DROP TABLE appy_ExoMenu');
        $this->addSql('DROP TABLE appy_LanguageQCM');
        $this->addSql('DROP TABLE appy_NiveauQCM');
        $this->addSql('DROP TABLE appy_PropositionIA');
        $this->addSql('DROP TABLE appy_QCM');
        $this->addSql('DROP TABLE appy_UserPageVisit');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_AC29E71BA76ED395');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_AC29E71BC4663E4');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_E46960F5A76ED395 FOREIGN KEY (user_id) REFERENCES appy_User (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_E46960F5C4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE appy_favorite RENAME INDEX idx_ac29e71bc4663e4 TO IDX_E46960F5C4663E4');
        $this->addSql('ALTER TABLE appy_favorite RENAME INDEX idx_ac29e71ba76ed395 TO IDX_E46960F5A76ED395');
    }
}
