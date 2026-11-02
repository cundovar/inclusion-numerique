<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Clés d'accès de l'assistant de rédaction (empreinte seulement) et auteur d'un cours.
 */
final class Version20261015102336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clés d\'accès des agents de rédaction ; auteur des cours';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE agent_token (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(60) NOT NULL, token_hash VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', revoked_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_669989435E237E06 (name), UNIQUE INDEX UNIQ_66998943B3BC57DA (token_hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appy_PageContent ADD author VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE agent_token');
        $this->addSql('ALTER TABLE appy_PageContent DROP author');
    }
}
