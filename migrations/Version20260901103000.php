<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260901103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Stores resumable AI course generations and their illustration metadata.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE agent_course_generation (id INT AUTO_INCREMENT NOT NULL, course_id INT DEFAULT NULL, batch_id VARCHAR(100) NOT NULL, external_id VARCHAR(100) NOT NULL, status VARCHAR(20) NOT NULL, verification_attempts INT NOT NULL, payload JSON NOT NULL, candidate JSON DEFAULT NULL, verification_report JSON DEFAULT NULL, technical_error LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', finished_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_AA2B6D49C4663E4 (course_id), UNIQUE INDEX generation_batch_item_unique (batch_id, external_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE course_media (id INT AUTO_INCREMENT NOT NULL, course_id INT DEFAULT NULL, generation_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, width INT NOT NULL, height INT NOT NULL, checksum VARCHAR(64) NOT NULL, alt_text VARCHAR(500) NOT NULL, caption LONGTEXT DEFAULT NULL, prompt LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_6A19AB17E6D9BD39 (filename), INDEX IDX_6A19AB17C4663E4 (course_id), INDEX IDX_6A19AB17DF0AC0F7 (generation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE agent_course_generation ADD CONSTRAINT FK_AA2B6D49C4663E4 FOREIGN KEY (course_id) REFERENCES appy_PageContent (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE course_media ADD CONSTRAINT FK_6A19AB17C4663E4 FOREIGN KEY (course_id) REFERENCES appy_PageContent (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE course_media ADD CONSTRAINT FK_6A19AB17DF0AC0F7 FOREIGN KEY (generation_id) REFERENCES agent_course_generation (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE course_media DROP FOREIGN KEY FK_6A19AB17C4663E4');
        $this->addSql('ALTER TABLE course_media DROP FOREIGN KEY FK_6A19AB17DF0AC0F7');
        $this->addSql('ALTER TABLE agent_course_generation DROP FOREIGN KEY FK_AA2B6D49C4663E4');
        $this->addSql('DROP TABLE course_media');
        $this->addSql('DROP TABLE agent_course_generation');
    }
}
