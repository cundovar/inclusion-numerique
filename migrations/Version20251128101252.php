<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251128101252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appy_DocDeCode (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_Logo (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, logo VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, docDeCode_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1EBF2ED0C703A8B4 (docDeCode_id), UNIQUE INDEX UNIQ_1EBF2ED012469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_PositionMenus (id INT AUTO_INCREMENT NOT NULL, position VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_Seo (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, metaDescription LONGTEXT NOT NULL, metaKeywords LONGTEXT DEFAULT NULL, ogTitle VARCHAR(255) DEFAULT NULL, ogDescription LONGTEXT DEFAULT NULL, ogImage VARCHAR(500) DEFAULT NULL, canonicalUrl VARCHAR(500) DEFAULT NULL, noIndex TINYINT(1) DEFAULT 0 NOT NULL, noFollow TINYINT(1) DEFAULT 0 NOT NULL, page VARCHAR(255) DEFAULT NULL, structuredData LONGTEXT DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_52F84C2E140AB620 (page), UNIQUE INDEX UNIQ_52F84C2E12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appy_Logo ADD CONSTRAINT FK_1EBF2ED0C703A8B4 FOREIGN KEY (docDeCode_id) REFERENCES appy_DocDeCode (id)');
        $this->addSql('ALTER TABLE appy_Logo ADD CONSTRAINT FK_1EBF2ED012469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)');
        $this->addSql('ALTER TABLE appy_Seo ADD CONSTRAINT FK_52F84C2E12469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_Category ADD couleur VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD positionMenus_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appy_Category ADD CONSTRAINT FK_3BD61255ACB895C FOREIGN KEY (positionMenus_id) REFERENCES appy_PositionMenus (id)');
        $this->addSql('CREATE INDEX IDX_3BD61255ACB895C ON appy_Category (positionMenus_id)');
        $this->addSql('ALTER TABLE appy_Menus ADD positionMenus_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appy_Menus ADD CONSTRAINT FK_70E2AA71ACB895C FOREIGN KEY (positionMenus_id) REFERENCES appy_PositionMenus (id)');
        $this->addSql('CREATE INDEX IDX_70E2AA71ACB895C ON appy_Menus (positionMenus_id)');
        $this->addSql('ALTER TABLE appy_Page ADD seo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appy_Page ADD CONSTRAINT FK_EE3B02E397E3DD86 FOREIGN KEY (seo_id) REFERENCES appy_Seo (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EE3B02E397E3DD86 ON appy_Page (seo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appy_Category DROP FOREIGN KEY FK_3BD61255ACB895C');
        $this->addSql('ALTER TABLE appy_Menus DROP FOREIGN KEY FK_70E2AA71ACB895C');
        $this->addSql('ALTER TABLE appy_Page DROP FOREIGN KEY FK_EE3B02E397E3DD86');
        $this->addSql('ALTER TABLE appy_Logo DROP FOREIGN KEY FK_1EBF2ED0C703A8B4');
        $this->addSql('ALTER TABLE appy_Logo DROP FOREIGN KEY FK_1EBF2ED012469DE2');
        $this->addSql('ALTER TABLE appy_Seo DROP FOREIGN KEY FK_52F84C2E12469DE2');
        $this->addSql('DROP TABLE appy_DocDeCode');
        $this->addSql('DROP TABLE appy_Logo');
        $this->addSql('DROP TABLE appy_PositionMenus');
        $this->addSql('DROP TABLE appy_Seo');
        $this->addSql('DROP INDEX IDX_3BD61255ACB895C ON appy_Category');
        $this->addSql('ALTER TABLE appy_Category DROP couleur, DROP description, DROP positionMenus_id');
        $this->addSql('DROP INDEX UNIQ_EE3B02E397E3DD86 ON appy_Page');
        $this->addSql('ALTER TABLE appy_Page DROP seo_id');
        $this->addSql('DROP INDEX IDX_70E2AA71ACB895C ON appy_Menus');
        $this->addSql('ALTER TABLE appy_Menus DROP positionMenus_id');
    }
}
