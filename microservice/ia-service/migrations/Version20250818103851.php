<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250818103851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE qcmanswer (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, selected_answer VARCHAR(10) NOT NULL, is_correct TINYINT(1) NOT NULL, answered_at DATETIME NOT NULL, time_spent INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_DECBA2C31E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE qcmquestion (id INT AUTO_INCREMENT NOT NULL, session_id INT NOT NULL, question LONGTEXT NOT NULL, options JSON NOT NULL, correct_answer VARCHAR(10) NOT NULL, explanation LONGTEXT DEFAULT NULL, page_content_id INT DEFAULT NULL, difficulty VARCHAR(50) DEFAULT NULL, topic VARCHAR(100) DEFAULT NULL, INDEX IDX_10027378613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE qcmsession (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, category_id INT DEFAULT NULL, difficulty VARCHAR(50) DEFAULT NULL, questions_count INT DEFAULT NULL, score INT DEFAULT NULL, total_questions INT DEFAULT NULL, completed_at DATETIME DEFAULT NULL, status VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE qcmanswer ADD CONSTRAINT FK_DECBA2C31E27F6BF FOREIGN KEY (question_id) REFERENCES qcmquestion (id)');
        $this->addSql('ALTER TABLE qcmquestion ADD CONSTRAINT FK_10027378613FECDF FOREIGN KEY (session_id) REFERENCES qcmsession (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE qcmanswer DROP FOREIGN KEY FK_DECBA2C31E27F6BF');
        $this->addSql('ALTER TABLE qcmquestion DROP FOREIGN KEY FK_10027378613FECDF');
        $this->addSql('DROP TABLE qcmanswer');
        $this->addSql('DROP TABLE qcmquestion');
        $this->addSql('DROP TABLE qcmsession');
    }
}
