<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251010000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create UserPageVisit table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appy_UserPageVisit (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, page_url VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, visited_at DATETIME NOT NULL, user_agent VARCHAR(255) DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, time_spent INT DEFAULT NULL, INDEX IDX_user_id (user_id), INDEX IDX_user_visited_at (user_id, visited_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appy_UserPageVisit ADD CONSTRAINT FK_UserPageVisit_User FOREIGN KEY (user_id) REFERENCES `appy_User` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appy_UserPageVisit DROP FOREIGN KEY FK_UserPageVisit_User');
        $this->addSql('DROP TABLE appy_UserPageVisit');
    }
}
