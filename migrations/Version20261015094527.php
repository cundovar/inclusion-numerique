<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Public visé par un parcours : « J'apprends le numérique » ou « J'accompagne des personnes ».
 */
final class Version20261015094527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le public visé (audience) aux catégories';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_Category ADD audience VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_Category DROP audience');
    }
}
