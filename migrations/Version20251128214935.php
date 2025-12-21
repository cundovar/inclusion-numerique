<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251128214935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appy_PageVisit (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, page_id INT NOT NULL, visitedAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', lastVisitedAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', visitCount INT NOT NULL, INDEX IDX_AF0167AAA76ED395 (user_id), INDEX IDX_AF0167AAC4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appy_PageVisit ADD CONSTRAINT FK_AF0167AAA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE appy_PageVisit ADD CONSTRAINT FK_AF0167AAC4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id)');
        $this->addSql('ALTER TABLE appy_User ADD trackPageVisits TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appy_PageVisit DROP FOREIGN KEY FK_AF0167AAA76ED395');
        $this->addSql('ALTER TABLE appy_PageVisit DROP FOREIGN KEY FK_AF0167AAC4663E4');
        $this->addSql('DROP TABLE appy_PageVisit');
        $this->addSql('ALTER TABLE `appy_User` DROP trackPageVisits');
    }
}
