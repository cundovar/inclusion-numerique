<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rang d'un cours dans son module : l'API et le site affichent les cours dans l'ordre pédagogique
 * au lieu de l'ordre d'ajout. Les cours sans rang passent après les autres.
 */
final class Version20261016000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rang des cours dans leur module (appy_PageContent.ordre)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_PageContent ADD ordre INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_PageContent DROP ordre');
    }
}
