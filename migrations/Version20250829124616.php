<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829124616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appy_ContentRecommendation (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, priority VARCHAR(20) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, suggestedContent LONGTEXT DEFAULT NULL, metadata JSON DEFAULT NULL, status VARCHAR(20) NOT NULL, appliedAt DATETIME DEFAULT NULL, createdAt DATETIME NOT NULL, pageContent_id INT NOT NULL, appliedBy_id INT DEFAULT NULL, INDEX IDX_792FD738BD8FBEEF (pageContent_id), INDEX IDX_792FD73852552E50 (appliedBy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_CourseAnalysis (id INT AUTO_INCREMENT NOT NULL, analysis JSON NOT NULL, summary LONGTEXT DEFAULT NULL, qualityScore DOUBLE PRECISION NOT NULL, difficultyLevel VARCHAR(20) NOT NULL, estimatedReadingTime INT NOT NULL, suggestions JSON NOT NULL, analyzedAt DATETIME NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, pageContent_id INT NOT NULL, INDEX IDX_8AA753B0BD8FBEEF (pageContent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_LearningPath (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, difficultyLevel VARCHAR(20) NOT NULL, estimatedDuration INT NOT NULL, courseSequence JSON NOT NULL, prerequisites JSON DEFAULT NULL, learningObjectives JSON DEFAULT NULL, status VARCHAR(20) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME DEFAULT NULL, targetUser_id INT DEFAULT NULL, createdBy_id INT DEFAULT NULL, INDEX IDX_324E6BF798EAEA3A (targetUser_id), INDEX IDX_324E6BF712469DE2 (category_id), INDEX IDX_324E6BF73174800F (createdBy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appy_UserLearningAnalytics (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, eventType VARCHAR(50) NOT NULL, timeSpent INT NOT NULL, comprehensionScore DOUBLE PRECISION DEFAULT NULL, interactionData JSON DEFAULT NULL, difficultyConcepts JSON DEFAULT NULL, preferredLearningStyle JSON DEFAULT NULL, eventDate DATETIME NOT NULL, createdAt DATETIME NOT NULL, pageContent_id INT DEFAULT NULL, learningPath_id INT DEFAULT NULL, INDEX IDX_9B07CDCBA76ED395 (user_id), INDEX IDX_9B07CDCBBD8FBEEF (pageContent_id), INDEX IDX_9B07CDCB2910C38D (learningPath_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appy_ContentRecommendation ADD CONSTRAINT FK_792FD738BD8FBEEF FOREIGN KEY (pageContent_id) REFERENCES appy_PageContent (id)');
        $this->addSql('ALTER TABLE appy_ContentRecommendation ADD CONSTRAINT FK_792FD73852552E50 FOREIGN KEY (appliedBy_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE appy_CourseAnalysis ADD CONSTRAINT FK_8AA753B0BD8FBEEF FOREIGN KEY (pageContent_id) REFERENCES appy_PageContent (id)');
        $this->addSql('ALTER TABLE appy_LearningPath ADD CONSTRAINT FK_324E6BF798EAEA3A FOREIGN KEY (targetUser_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE appy_LearningPath ADD CONSTRAINT FK_324E6BF712469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)');
        $this->addSql('ALTER TABLE appy_LearningPath ADD CONSTRAINT FK_324E6BF73174800F FOREIGN KEY (createdBy_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE appy_UserLearningAnalytics ADD CONSTRAINT FK_9B07CDCBA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE appy_UserLearningAnalytics ADD CONSTRAINT FK_9B07CDCBBD8FBEEF FOREIGN KEY (pageContent_id) REFERENCES appy_PageContent (id)');
        $this->addSql('ALTER TABLE appy_UserLearningAnalytics ADD CONSTRAINT FK_9B07CDCB2910C38D FOREIGN KEY (learningPath_id) REFERENCES appy_LearningPath (id)');
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
        $this->addSql('ALTER TABLE appy_ContentRecommendation DROP FOREIGN KEY FK_792FD738BD8FBEEF');
        $this->addSql('ALTER TABLE appy_ContentRecommendation DROP FOREIGN KEY FK_792FD73852552E50');
        $this->addSql('ALTER TABLE appy_CourseAnalysis DROP FOREIGN KEY FK_8AA753B0BD8FBEEF');
        $this->addSql('ALTER TABLE appy_LearningPath DROP FOREIGN KEY FK_324E6BF798EAEA3A');
        $this->addSql('ALTER TABLE appy_LearningPath DROP FOREIGN KEY FK_324E6BF712469DE2');
        $this->addSql('ALTER TABLE appy_LearningPath DROP FOREIGN KEY FK_324E6BF73174800F');
        $this->addSql('ALTER TABLE appy_UserLearningAnalytics DROP FOREIGN KEY FK_9B07CDCBA76ED395');
        $this->addSql('ALTER TABLE appy_UserLearningAnalytics DROP FOREIGN KEY FK_9B07CDCBBD8FBEEF');
        $this->addSql('ALTER TABLE appy_UserLearningAnalytics DROP FOREIGN KEY FK_9B07CDCB2910C38D');
        $this->addSql('DROP TABLE appy_ContentRecommendation');
        $this->addSql('DROP TABLE appy_CourseAnalysis');
        $this->addSql('DROP TABLE appy_LearningPath');
        $this->addSql('DROP TABLE appy_UserLearningAnalytics');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_AC29E71BA76ED395');
        $this->addSql('ALTER TABLE appy_favorite DROP FOREIGN KEY FK_AC29E71BC4663E4');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_E46960F5A76ED395 FOREIGN KEY (user_id) REFERENCES appy_User (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE appy_favorite ADD CONSTRAINT FK_E46960F5C4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE appy_favorite RENAME INDEX idx_ac29e71bc4663e4 TO IDX_E46960F5C4663E4');
        $this->addSql('ALTER TABLE appy_favorite RENAME INDEX idx_ac29e71ba76ed395 TO IDX_E46960F5A76ED395');
    }
}
