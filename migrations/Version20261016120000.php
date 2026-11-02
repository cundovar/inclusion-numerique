<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rang d'un parcours dans le catalogue de son public : les parcours s'affichent dans l'ordre voulu
 * au lieu de l'ordre alphabétique. Les parcours sans rang passent après les autres.
 */
final class Version20261016120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rang des parcours dans le catalogue (appy_Category.ordre)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_Category ADD ordre INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_Category DROP ordre');
    }
}
