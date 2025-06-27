<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250623145456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE page_content ADD category_id INT DEFAULT NULL, ADD menu_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE page_content ADD CONSTRAINT FK_4A5DB3C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE page_content ADD CONSTRAINT FK_4A5DB3CCCD7E912 FOREIGN KEY (menu_id) REFERENCES menus (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4A5DB3C12469DE2 ON page_content (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4A5DB3CCCD7E912 ON page_content (menu_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE page_content DROP FOREIGN KEY FK_4A5DB3C12469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE page_content DROP FOREIGN KEY FK_4A5DB3CCCD7E912
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4A5DB3C12469DE2 ON page_content
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4A5DB3CCCD7E912 ON page_content
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE page_content DROP category_id, DROP menu_id
        SQL);
    }
}
