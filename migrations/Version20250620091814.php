<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250620091814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE pages (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lesson ADD page_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3C4663E4 FOREIGN KEY (page_id) REFERENCES pages (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F87474F3C4663E4 ON lesson (page_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3C4663E4
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE pages
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F87474F3C4663E4 ON lesson
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lesson DROP page_id
        SQL);
    }
}
