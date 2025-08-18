<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250818103542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_E46960F5A76ED395');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_E46960F5C4663E4');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_AC29E71BA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_AC29E71BC4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_favorite RENAME INDEX idx_e46960f5a76ed395 TO IDX_AC29E71BA76ED395');
        $this->addSql('ALTER TABLE appy_favorite RENAME INDEX idx_e46960f5c4663e4 TO IDX_AC29E71BC4663E4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_AC29E71BA76ED395');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_AC29E71BC4663E4');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_E46960F5A76ED395 FOREIGN KEY (user_id) REFERENCES appy_User (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_E46960F5C4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE appy_favorite RENAME INDEX idx_ac29e71bc4663e4 TO IDX_E46960F5C4663E4');
        $this->addSql('ALTER TABLE appy_favorite RENAME INDEX idx_ac29e71ba76ed395 TO IDX_E46960F5A76ED395');
    }
}
