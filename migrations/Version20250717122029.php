<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717122029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE appy_User
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5C4663E4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5C4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE appy_User (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, roles JSON NOT NULL, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `user`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5C4663E4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5A76ED395 FOREIGN KEY (user_id) REFERENCES appy_User (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5C4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
