<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Miniature d'un parcours : une illustration du back-office (course_media) affichée sur sa carte.
 * Si l'image est supprimée, le parcours reste, sans miniature.
 */
final class Version20261016150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Miniature des parcours (appy_Category.illustration_id)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_Category ADD illustration_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appy_Category ADD CONSTRAINT FK_3BD612555926566C FOREIGN KEY (illustration_id) REFERENCES course_media (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3BD612555926566C ON appy_Category (illustration_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_Category DROP FOREIGN KEY FK_3BD612555926566C');
        $this->addSql('DROP INDEX IDX_3BD612555926566C ON appy_Category');
        $this->addSql('ALTER TABLE appy_Category DROP illustration_id');
    }
}
