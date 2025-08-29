<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add AI pedagogical entities: CourseAnalysis, ContentRecommendation, LearningPath, UserLearningAnalytics';
    }

    public function up(Schema $schema): void
    {
        // Course Analysis table
        $this->addSql('CREATE TABLE course_analysis (
            id INT AUTO_INCREMENT NOT NULL,
            page_content_id INT NOT NULL,
            analysis JSON NOT NULL,
            summary LONGTEXT DEFAULT NULL,
            quality_score DOUBLE PRECISION NOT NULL,
            difficulty_level VARCHAR(20) NOT NULL,
            estimated_reading_time INT NOT NULL,
            suggestions JSON NOT NULL,
            analyzed_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            INDEX IDX_course_analysis_page_content (page_content_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Content Recommendation table
        $this->addSql('CREATE TABLE content_recommendation (
            id INT AUTO_INCREMENT NOT NULL,
            page_content_id INT NOT NULL,
            applied_by_id INT DEFAULT NULL,
            type VARCHAR(50) NOT NULL,
            priority VARCHAR(20) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            suggested_content LONGTEXT DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            applied_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_content_recommendation_page_content (page_content_id),
            INDEX IDX_content_recommendation_applied_by (applied_by_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Learning Path table
        $this->addSql('CREATE TABLE learning_path (
            id INT AUTO_INCREMENT NOT NULL,
            target_user_id INT DEFAULT NULL,
            category_id INT DEFAULT NULL,
            created_by_id INT DEFAULT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            difficulty_level VARCHAR(20) NOT NULL,
            estimated_duration INT NOT NULL,
            course_sequence JSON NOT NULL,
            prerequisites JSON DEFAULT NULL,
            learning_objectives JSON DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            INDEX IDX_learning_path_target_user (target_user_id),
            INDEX IDX_learning_path_category (category_id),
            INDEX IDX_learning_path_created_by (created_by_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // User Learning Analytics table
        $this->addSql('CREATE TABLE user_learning_analytics (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            page_content_id INT DEFAULT NULL,
            learning_path_id INT DEFAULT NULL,
            event_type VARCHAR(50) NOT NULL,
            time_spent INT NOT NULL,
            comprehension_score DOUBLE PRECISION DEFAULT NULL,
            interaction_data JSON DEFAULT NULL,
            difficulty_concepts JSON DEFAULT NULL,
            preferred_learning_style JSON DEFAULT NULL,
            event_date DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_user_learning_analytics_user (user_id),
            INDEX IDX_user_learning_analytics_page_content (page_content_id),
            INDEX IDX_user_learning_analytics_learning_path (learning_path_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Foreign key constraints
        $this->addSql('ALTER TABLE course_analysis ADD CONSTRAINT FK_course_analysis_page_content FOREIGN KEY (page_content_id) REFERENCES page_content (id)');
        
        $this->addSql('ALTER TABLE content_recommendation ADD CONSTRAINT FK_content_recommendation_page_content FOREIGN KEY (page_content_id) REFERENCES page_content (id)');
        $this->addSql('ALTER TABLE content_recommendation ADD CONSTRAINT FK_content_recommendation_applied_by FOREIGN KEY (applied_by_id) REFERENCES `appy_User` (id)');
        
        $this->addSql('ALTER TABLE learning_path ADD CONSTRAINT FK_learning_path_target_user FOREIGN KEY (target_user_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE learning_path ADD CONSTRAINT FK_learning_path_category FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE learning_path ADD CONSTRAINT FK_learning_path_created_by FOREIGN KEY (created_by_id) REFERENCES `appy_User` (id)');
        
        $this->addSql('ALTER TABLE user_learning_analytics ADD CONSTRAINT FK_user_learning_analytics_user FOREIGN KEY (user_id) REFERENCES `appy_User` (id)');
        $this->addSql('ALTER TABLE user_learning_analytics ADD CONSTRAINT FK_user_learning_analytics_page_content FOREIGN KEY (page_content_id) REFERENCES page_content (id)');
        $this->addSql('ALTER TABLE user_learning_analytics ADD CONSTRAINT FK_user_learning_analytics_learning_path FOREIGN KEY (learning_path_id) REFERENCES learning_path (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop tables in reverse order due to foreign key constraints
        $this->addSql('DROP TABLE user_learning_analytics');
        $this->addSql('DROP TABLE learning_path');
        $this->addSql('DROP TABLE content_recommendation');
        $this->addSql('DROP TABLE course_analysis');
    }
}