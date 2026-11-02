<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Relecture d'un brouillon par un second agent : avis, résumé, constats, relecteur et date.
 */
final class Version20261015103118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Relecture des brouillons par un second agent';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_PageContent ADD review_report JSON DEFAULT NULL, ADD reviewed_by VARCHAR(100) DEFAULT NULL, ADD reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appy_PageContent DROP review_report, DROP reviewed_by, DROP reviewed_at');
    }
}
