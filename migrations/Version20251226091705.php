<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251226091705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appy_Category ADD superMenu_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appy_Category ADD CONSTRAINT FK_3BD6125537BAB3B4 FOREIGN KEY (superMenu_id) REFERENCES appy_SuperMenu (id)');
        $this->addSql('CREATE INDEX IDX_3BD6125537BAB3B4 ON appy_Category (superMenu_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appy_Category DROP FOREIGN KEY FK_3BD6125537BAB3B4');
        $this->addSql('DROP INDEX IDX_3BD6125537BAB3B4 ON appy_Category');
        $this->addSql('ALTER TABLE appy_Category DROP superMenu_id');
    }
}
