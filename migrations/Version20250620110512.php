<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250620110512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE menu ADD page_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu ADD CONSTRAINT FK_7D053A93C4663E4 FOREIGN KEY (page_id) REFERENCES pages (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7D053A93C4663E4 ON menu (page_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_C242628989D9B62 ON module
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE module DROP slug, DROP description
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE menu DROP FOREIGN KEY FK_7D053A93C4663E4
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7D053A93C4663E4 ON menu
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu DROP page_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE module ADD slug VARCHAR(255) NOT NULL, ADD description LONGTEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_C242628989D9B62 ON module (slug)
        SQL);
    }
}
