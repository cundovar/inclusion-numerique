<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260116133748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appy_Category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, couleur VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, visible TINYINT(1) DEFAULT 1, positionMenus_id INT DEFAULT NULL, superMenu_id INT DEFAULT NULL, niveauCours_id INT DEFAULT NULL, INDEX IDX_3BD61255ACB895C (positionMenus_id), INDEX IDX_3BD6125537BAB3B4 (superMenu_id), INDEX IDX_3BD6125593E541ED (niveauCours_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_ChoicesQCM (id INT AUTO_INCREMENT NOT NULL, qcm_id INT NOT NULL, question VARCHAR(255) NOT NULL, isCorrect TINYINT(1) NOT NULL, explication LONGTEXT DEFAULT NULL, INDEX IDX_B3840B43FF6241A6 (qcm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_DocDeCode (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_Exo (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(100) NOT NULL, exoMenu_id INT DEFAULT NULL, INDEX IDX_B53FFFF08F27B68 (exoMenu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_ExoBlock (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT DEFAULT NULL, code LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, exoContent_id INT DEFAULT NULL, INDEX IDX_626E16DAD5B57270 (exoContent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_ExoContent (id INT AUTO_INCREMENT NOT NULL, exo_id INT DEFAULT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(50) DEFAULT NULL, content LONGTEXT DEFAULT NULL, code LONGTEXT DEFAULT NULL, exoMenu_id INT DEFAULT NULL, INDEX IDX_B81F09DFDA1C6F33 (exo_id), INDEX IDX_B81F09DF12469DE2 (category_id), INDEX IDX_B81F09DF8F27B68 (exoMenu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_ExoMenu (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, label VARCHAR(100) DEFAULT NULL, INDEX IDX_61CDAD812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_LanguageQCM (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_Logo (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, logo VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, docDeCode_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1EBF2ED0C703A8B4 (docDeCode_id), UNIQUE INDEX UNIQ_1EBF2ED012469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_Menus (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, label VARCHAR(100) DEFAULT NULL, positionMenus_id INT DEFAULT NULL, niveauCours_id INT DEFAULT NULL, INDEX IDX_70E2AA7112469DE2 (category_id), INDEX IDX_70E2AA71ACB895C (positionMenus_id), INDEX IDX_70E2AA7193E541ED (niveauCours_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_NiveauCours (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, ordre INT DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_NiveauQCM (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_Note (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, page_id INT NOT NULL, content LONGTEXT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_358C4ED7A76ED395 (user_id), INDEX IDX_358C4ED7C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_Page (id INT AUTO_INCREMENT NOT NULL, menus_id INT DEFAULT NULL, seo_id INT DEFAULT NULL, slug VARCHAR(100) NOT NULL, INDEX IDX_EE3B02E314041B84 (menus_id), UNIQUE INDEX UNIQ_EE3B02E397E3DD86 (seo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_PageBlock (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT DEFAULT NULL, code LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, pageContent_id INT DEFAULT NULL, INDEX IDX_6F6419B1BD8FBEEF (pageContent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_PageContent (id INT AUTO_INCREMENT NOT NULL, page_id INT DEFAULT NULL, category_id INT DEFAULT NULL, menu_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(50) DEFAULT NULL, content LONGTEXT DEFAULT NULL, code LONGTEXT DEFAULT NULL, visible TINYINT(1) DEFAULT 1, niveauCours_id INT DEFAULT NULL, INDEX IDX_AEA9AB28C4663E4 (page_id), INDEX IDX_AEA9AB2812469DE2 (category_id), INDEX IDX_AEA9AB28CCD7E912 (menu_id), INDEX IDX_AEA9AB2893E541ED (niveauCours_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_PositionMenus (id INT AUTO_INCREMENT NOT NULL, position VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_PropositionIA (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(50) NOT NULL, statut VARCHAR(20) NOT NULL, payload JSON DEFAULT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_QCM (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) DEFAULT NULL, solution LONGTEXT NOT NULL, languageQCM_id INT DEFAULT NULL, niveauQCM_id INT DEFAULT NULL, INDEX IDX_47C25A806C5D2C67 (languageQCM_id), INDEX IDX_47C25A80F662DA50 (niveauQCM_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_Seo (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, metaDescription LONGTEXT NOT NULL, metaKeywords LONGTEXT DEFAULT NULL, ogTitle VARCHAR(255) DEFAULT NULL, ogDescription LONGTEXT DEFAULT NULL, ogImage VARCHAR(500) DEFAULT NULL, canonicalUrl VARCHAR(500) DEFAULT NULL, noIndex TINYINT(1) DEFAULT 0 NOT NULL, noFollow TINYINT(1) DEFAULT 0 NOT NULL, page VARCHAR(255) DEFAULT NULL, structuredData LONGTEXT DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_52F84C2E140AB620 (page), UNIQUE INDEX UNIQ_52F84C2E12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_SiteConfiguration (id INT AUTO_INCREMENT NOT NULL, configKey VARCHAR(255) NOT NULL, settings JSON NOT NULL, updatedAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_SuperMenu (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `appy_User` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, trackPageVisits TINYINT(1) DEFAULT 1 NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_UserCustomization (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, settings JSON NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_FBA50EFDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_UserPageVisit (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, page_url VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, visited_at DATETIME NOT NULL, user_agent VARCHAR(255) DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, time_spent INT DEFAULT NULL, INDEX IDX_7D09FA76A76ED395 (user_id), INDEX IDX_7D09FA76A76ED395EDA764E3 (user_id, visited_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_favorite (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, page_id INT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_AC29E71BA76ED395 (user_id), INDEX IDX_AC29E71BC4663E4 (page_id), UNIQUE INDEX user_page_unique (user_id, page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appy_Category ADD CONSTRAINT FK_3BD61255ACB895C FOREIGN KEY (positionMenus_id) REFERENCES appy_PositionMenus (id)');
        $this->addSql('ALTER TABLE appy_Category ADD CONSTRAINT FK_3BD6125537BAB3B4 FOREIGN KEY (superMenu_id) REFERENCES appy_SuperMenu (id)');
        $this->addSql('ALTER TABLE appy_Category ADD CONSTRAINT FK_3BD6125593E541ED FOREIGN KEY (niveauCours_id) REFERENCES appy_NiveauCours (id)');
        $this->addSql('ALTER TABLE appy_ChoicesQCM ADD CONSTRAINT FK_B3840B43FF6241A6 FOREIGN KEY (qcm_id) REFERENCES appy_QCM (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_Exo ADD CONSTRAINT FK_B53FFFF08F27B68 FOREIGN KEY (exoMenu_id) REFERENCES appy_ExoMenu (id)');
        $this->addSql('ALTER TABLE appy_ExoBlock ADD CONSTRAINT FK_626E16DAD5B57270 FOREIGN KEY (exoContent_id) REFERENCES appy_ExoContent (id)');
        $this->addSql('ALTER TABLE appy_ExoContent ADD CONSTRAINT FK_B81F09DFDA1C6F33 FOREIGN KEY (exo_id) REFERENCES appy_Exo (id)');
        $this->addSql('ALTER TABLE appy_ExoContent ADD CONSTRAINT FK_B81F09DF12469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)');
        $this->addSql('ALTER TABLE appy_ExoContent ADD CONSTRAINT FK_B81F09DF8F27B68 FOREIGN KEY (exoMenu_id) REFERENCES appy_ExoMenu (id)');
        $this->addSql('ALTER TABLE appy_ExoMenu ADD CONSTRAINT FK_61CDAD812469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)');
        $this->addSql('ALTER TABLE appy_Logo ADD CONSTRAINT FK_1EBF2ED0C703A8B4 FOREIGN KEY (docDeCode_id) REFERENCES appy_DocDeCode (id)');
        $this->addSql('ALTER TABLE appy_Logo ADD CONSTRAINT FK_1EBF2ED012469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)');
        $this->addSql('ALTER TABLE appy_Menus ADD CONSTRAINT FK_70E2AA7112469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)');
        $this->addSql('ALTER TABLE appy_Menus ADD CONSTRAINT FK_70E2AA71ACB895C FOREIGN KEY (positionMenus_id) REFERENCES appy_PositionMenus (id)');
        $this->addSql('ALTER TABLE appy_Menus ADD CONSTRAINT FK_70E2AA7193E541ED FOREIGN KEY (niveauCours_id) REFERENCES appy_NiveauCours (id)');
        $this->addSql('ALTER TABLE appy_Note ADD CONSTRAINT FK_358C4ED7A76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE appy_Note ADD CONSTRAINT FK_358C4ED7C4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id)');
        $this->addSql('ALTER TABLE appy_Page ADD CONSTRAINT FK_EE3B02E314041B84 FOREIGN KEY (menus_id) REFERENCES appy_Menus (id)');
        $this->addSql('ALTER TABLE appy_Page ADD CONSTRAINT FK_EE3B02E397E3DD86 FOREIGN KEY (seo_id) REFERENCES appy_Seo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_PageBlock ADD CONSTRAINT FK_6F6419B1BD8FBEEF FOREIGN KEY (pageContent_id) REFERENCES appy_PageContent (id)');
        $this->addSql('ALTER TABLE appy_PageContent ADD CONSTRAINT FK_AEA9AB28C4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id)');
        $this->addSql('ALTER TABLE appy_PageContent ADD CONSTRAINT FK_AEA9AB2812469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)');
        $this->addSql('ALTER TABLE appy_PageContent ADD CONSTRAINT FK_AEA9AB28CCD7E912 FOREIGN KEY (menu_id) REFERENCES appy_Menus (id)');
        $this->addSql('ALTER TABLE appy_PageContent ADD CONSTRAINT FK_AEA9AB2893E541ED FOREIGN KEY (niveauCours_id) REFERENCES appy_NiveauCours (id)');
        $this->addSql('ALTER TABLE appy_QCM ADD CONSTRAINT FK_47C25A806C5D2C67 FOREIGN KEY (languageQCM_id) REFERENCES appy_LanguageQCM (id)');
        $this->addSql('ALTER TABLE appy_QCM ADD CONSTRAINT FK_47C25A80F662DA50 FOREIGN KEY (niveauQCM_id) REFERENCES appy_NiveauQCM (id)');
        $this->addSql('ALTER TABLE appy_Seo ADD CONSTRAINT FK_52F84C2E12469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_UserCustomization ADD CONSTRAINT FK_FBA50EFDA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE appy_UserPageVisit ADD CONSTRAINT FK_7D09FA76A76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_AC29E71BA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_AC29E71BC4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appy_Category DROP FOREIGN KEY FK_3BD61255ACB895C');
        $this->addSql('ALTER TABLE appy_Category DROP FOREIGN KEY FK_3BD6125537BAB3B4');
        $this->addSql('ALTER TABLE appy_Category DROP FOREIGN KEY FK_3BD6125593E541ED');
        $this->addSql('ALTER TABLE appy_ChoicesQCM DROP FOREIGN KEY FK_B3840B43FF6241A6');
        $this->addSql('ALTER TABLE appy_Exo DROP FOREIGN KEY FK_B53FFFF08F27B68');
        $this->addSql('ALTER TABLE appy_ExoBlock DROP FOREIGN KEY FK_626E16DAD5B57270');
        $this->addSql('ALTER TABLE appy_ExoContent DROP FOREIGN KEY FK_B81F09DFDA1C6F33');
        $this->addSql('ALTER TABLE appy_ExoContent DROP FOREIGN KEY FK_B81F09DF12469DE2');
        $this->addSql('ALTER TABLE appy_ExoContent DROP FOREIGN KEY FK_B81F09DF8F27B68');
        $this->addSql('ALTER TABLE appy_ExoMenu DROP FOREIGN KEY FK_61CDAD812469DE2');
        $this->addSql('ALTER TABLE appy_Logo DROP FOREIGN KEY FK_1EBF2ED0C703A8B4');
        $this->addSql('ALTER TABLE appy_Logo DROP FOREIGN KEY FK_1EBF2ED012469DE2');
        $this->addSql('ALTER TABLE appy_Menus DROP FOREIGN KEY FK_70E2AA7112469DE2');
        $this->addSql('ALTER TABLE appy_Menus DROP FOREIGN KEY FK_70E2AA71ACB895C');
        $this->addSql('ALTER TABLE appy_Menus DROP FOREIGN KEY FK_70E2AA7193E541ED');
        $this->addSql('ALTER TABLE appy_Note DROP FOREIGN KEY FK_358C4ED7A76ED395');
        $this->addSql('ALTER TABLE appy_Note DROP FOREIGN KEY FK_358C4ED7C4663E4');
        $this->addSql('ALTER TABLE appy_Page DROP FOREIGN KEY FK_EE3B02E314041B84');
        $this->addSql('ALTER TABLE appy_Page DROP FOREIGN KEY FK_EE3B02E397E3DD86');
        $this->addSql('ALTER TABLE appy_PageBlock DROP FOREIGN KEY FK_6F6419B1BD8FBEEF');
        $this->addSql('ALTER TABLE appy_PageContent DROP FOREIGN KEY FK_AEA9AB28C4663E4');
        $this->addSql('ALTER TABLE appy_PageContent DROP FOREIGN KEY FK_AEA9AB2812469DE2');
        $this->addSql('ALTER TABLE appy_PageContent DROP FOREIGN KEY FK_AEA9AB28CCD7E912');
        $this->addSql('ALTER TABLE appy_PageContent DROP FOREIGN KEY FK_AEA9AB2893E541ED');
        $this->addSql('ALTER TABLE appy_QCM DROP FOREIGN KEY FK_47C25A806C5D2C67');
        $this->addSql('ALTER TABLE appy_QCM DROP FOREIGN KEY FK_47C25A80F662DA50');
        $this->addSql('ALTER TABLE appy_Seo DROP FOREIGN KEY FK_52F84C2E12469DE2');
        $this->addSql('ALTER TABLE appy_UserCustomization DROP FOREIGN KEY FK_FBA50EFDA76ED395');
        $this->addSql('ALTER TABLE appy_UserPageVisit DROP FOREIGN KEY FK_7D09FA76A76ED395');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_AC29E71BA76ED395');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_AC29E71BC4663E4');
        $this->addSql('DROP TABLE appy_Category');
        $this->addSql('DROP TABLE appy_ChoicesQCM');
        $this->addSql('DROP TABLE appy_DocDeCode');
        $this->addSql('DROP TABLE appy_Exo');
        $this->addSql('DROP TABLE appy_ExoBlock');
        $this->addSql('DROP TABLE appy_ExoContent');
        $this->addSql('DROP TABLE appy_ExoMenu');
        $this->addSql('DROP TABLE appy_LanguageQCM');
        $this->addSql('DROP TABLE appy_Logo');
        $this->addSql('DROP TABLE appy_Menus');
        $this->addSql('DROP TABLE appy_NiveauCours');
        $this->addSql('DROP TABLE appy_NiveauQCM');
        $this->addSql('DROP TABLE appy_Note');
        $this->addSql('DROP TABLE appy_Page');
        $this->addSql('DROP TABLE appy_PageBlock');
        $this->addSql('DROP TABLE appy_PageContent');
        $this->addSql('DROP TABLE appy_PositionMenus');
        $this->addSql('DROP TABLE appy_PropositionIA');
        $this->addSql('DROP TABLE appy_QCM');
        $this->addSql('DROP TABLE appy_Seo');
        $this->addSql('DROP TABLE appy_SiteConfiguration');
        $this->addSql('DROP TABLE appy_SuperMenu');
        $this->addSql('DROP TABLE `appy_User`');
        $this->addSql('DROP TABLE appy_UserCustomization');
        $this->addSql('DROP TABLE appy_UserPageVisit');
        $this->addSql('DROP TABLE appy_favorite');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
