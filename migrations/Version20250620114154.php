<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250620114154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE module ADD menu_id INT DEFAULT NULL, ADD pages_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE module ADD CONSTRAINT FK_C242628CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE module ADD CONSTRAINT FK_C242628401ADD27 FOREIGN KEY (pages_id) REFERENCES pages (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C242628CCD7E912 ON module (menu_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C242628401ADD27 ON module (pages_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE module DROP FOREIGN KEY FK_C242628CCD7E912
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE module DROP FOREIGN KEY FK_C242628401ADD27
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C242628CCD7E912 ON module
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C242628401ADD27 ON module
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE module DROP menu_id, DROP pages_id
        SQL);
    }
}
