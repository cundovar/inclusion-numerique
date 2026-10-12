<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261012124537 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table revision (AgentCourseRevision), fixe le défaut de appy_NiveauCours.ordre et aligne les noms d\'index de course_media et agent_course_generation sur le mapping';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE revision (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, type_revision VARCHAR(50) NOT NULL, commentaire LONGTEXT DEFAULT NULL, ancien_code LONGTEXT DEFAULT NULL, nouveau_code LONGTEXT DEFAULT NULL, date_revision DATETIME NOT NULL, appliquee TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_6D6315CC591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE revision ADD CONSTRAINT FK_6D6315CC591CC992 FOREIGN KEY (course_id) REFERENCES appy_PageContent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agent_course_generation RENAME INDEX idx_aa2b6d49c4663e4 TO IDX_27DEF7CB591CC992');
        $this->addSql('ALTER TABLE appy_NiveauCours CHANGE ordre ordre INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE course_media RENAME INDEX uniq_6a19ab17e6d9bd39 TO UNIQ_8387A6E43C0BE965');
        $this->addSql('ALTER TABLE course_media RENAME INDEX idx_6a19ab17c4663e4 TO IDX_8387A6E4591CC992');
        $this->addSql('ALTER TABLE course_media RENAME INDEX idx_6a19ab17df0ac0f7 TO IDX_8387A6E4553A6EC4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE revision DROP FOREIGN KEY FK_6D6315CC591CC992');
        $this->addSql('DROP TABLE revision');
        $this->addSql('ALTER TABLE agent_course_generation RENAME INDEX idx_27def7cb591cc992 TO IDX_AA2B6D49C4663E4');
        $this->addSql('ALTER TABLE appy_NiveauCours CHANGE ordre ordre INT NOT NULL');
        $this->addSql('ALTER TABLE course_media RENAME INDEX idx_8387a6e4553a6ec4 TO IDX_6A19AB17DF0AC0F7');
        $this->addSql('ALTER TABLE course_media RENAME INDEX idx_8387a6e4591cc992 TO IDX_6A19AB17C4663E4');
        $this->addSql('ALTER TABLE course_media RENAME INDEX uniq_8387a6e43c0be965 TO UNIQ_6A19AB17E6D9BD39');
    }
}
