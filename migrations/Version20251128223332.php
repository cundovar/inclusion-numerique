<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251128223332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appy_Note (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, page_id INT NOT NULL, content LONGTEXT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_358C4ED7A76ED395 (user_id), INDEX IDX_358C4ED7C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appy_Note ADD CONSTRAINT FK_358C4ED7A76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE appy_Note ADD CONSTRAINT FK_358C4ED7C4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appy_Note DROP FOREIGN KEY FK_358C4ED7A76ED395');
        $this->addSql('ALTER TABLE appy_Note DROP FOREIGN KEY FK_358C4ED7C4663E4');
        $this->addSql('DROP TABLE appy_Note');
    }
}
