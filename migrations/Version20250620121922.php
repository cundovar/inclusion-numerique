<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250620121922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE module DROP FOREIGN KEY FK_C242628401ADD27
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C242628401ADD27 ON module
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE module DROP pages_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pages ADD modules_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pages ADD CONSTRAINT FK_2074E57560D6DC42 FOREIGN KEY (modules_id) REFERENCES module (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_2074E57560D6DC42 ON pages (modules_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE pages DROP FOREIGN KEY FK_2074E57560D6DC42
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_2074E57560D6DC42 ON pages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pages DROP modules_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE module ADD pages_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE module ADD CONSTRAINT FK_C242628401ADD27 FOREIGN KEY (pages_id) REFERENCES pages (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C242628401ADD27 ON module (pages_id)
        SQL);
    }
}
