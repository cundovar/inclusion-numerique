<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Connexion des apprenants par adresse e-mail ; le pseudo reste le nom affiché.
 * Facultative en base : les comptes existants du back-office continuent de se connecter avec leur pseudo.
 */
final class Version20261016200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adresse e-mail de connexion des comptes (appy_User.email, unique)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `appy_User` ADD email VARCHAR(180) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_77A2628AE7927C74 ON `appy_User` (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_77A2628AE7927C74 ON `appy_User`');
        $this->addSql('ALTER TABLE `appy_User` DROP email');
    }
}
