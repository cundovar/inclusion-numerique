<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250708074344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE appy_Category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE appy_Menus (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, label VARCHAR(100) DEFAULT NULL, INDEX IDX_70E2AA7112469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE appy_Page (id INT AUTO_INCREMENT NOT NULL, menus_id INT DEFAULT NULL, slug VARCHAR(100) NOT NULL, INDEX IDX_EE3B02E314041B84 (menus_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE appy_PageBlock (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT DEFAULT NULL, code LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, pageContent_id INT DEFAULT NULL, INDEX IDX_6F6419B1BD8FBEEF (pageContent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE appy_PageContent (id INT AUTO_INCREMENT NOT NULL, page_id INT DEFAULT NULL, category_id INT DEFAULT NULL, menu_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(50) DEFAULT NULL, content LONGTEXT DEFAULT NULL, code LONGTEXT DEFAULT NULL, INDEX IDX_AEA9AB28C4663E4 (page_id), INDEX IDX_AEA9AB2812469DE2 (category_id), INDEX IDX_AEA9AB28CCD7E912 (menu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE appy_User (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_Menus ADD CONSTRAINT FK_70E2AA7112469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_Page ADD CONSTRAINT FK_EE3B02E314041B84 FOREIGN KEY (menus_id) REFERENCES appy_Menus (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_PageBlock ADD CONSTRAINT FK_6F6419B1BD8FBEEF FOREIGN KEY (pageContent_id) REFERENCES appy_PageContent (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_PageContent ADD CONSTRAINT FK_AEA9AB28C4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_PageContent ADD CONSTRAINT FK_AEA9AB2812469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_PageContent ADD CONSTRAINT FK_AEA9AB28CCD7E912 FOREIGN KEY (menu_id) REFERENCES appy_Menus (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_Menus DROP FOREIGN KEY FK_70E2AA7112469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_Page DROP FOREIGN KEY FK_EE3B02E314041B84
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_PageBlock DROP FOREIGN KEY FK_6F6419B1BD8FBEEF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_PageContent DROP FOREIGN KEY FK_AEA9AB28C4663E4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_PageContent DROP FOREIGN KEY FK_AEA9AB2812469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appy_PageContent DROP FOREIGN KEY FK_AEA9AB28CCD7E912
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE appy_Category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE appy_Menus
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE appy_Page
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE appy_PageBlock
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE appy_PageContent
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE appy_User
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
