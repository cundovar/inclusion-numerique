<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Connecteur MCP par OAuth (claude.ai, ChatGPT) : connecteurs enregistrés, codes d'autorisation et accès
 * (empreintes seulement). Les clés d'accès des agents (agent_token) disparaissent avec l'ancien serveur MCP local.
 */
final class Version20261015114521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Connecteur MCP : tables OAuth ; suppression des clés d\'accès des agents';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE oauth_authorization_code (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT NOT NULL, code_hash VARCHAR(64) NOT NULL, redirect_uri VARCHAR(500) NOT NULL, code_challenge VARCHAR(128) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_793B0817E7530879 (code_hash), INDEX IDX_793B081719EB6921 (client_id), INDEX IDX_793B0817A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_client (id INT AUTO_INCREMENT NOT NULL, client_id VARCHAR(64) NOT NULL, name VARCHAR(60) NOT NULL, redirect_uris JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_AD73274D19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_grant (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT NOT NULL, authorization_code_id INT DEFAULT NULL, access_token_hash VARCHAR(64) NOT NULL, access_expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', refresh_token_hash VARCHAR(64) NOT NULL, refresh_expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', revoked_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_4E068C5D9982CF5B (access_token_hash), UNIQUE INDEX UNIQ_4E068C5D7288F9EC (refresh_token_hash), INDEX IDX_4E068C5D19EB6921 (client_id), INDEX IDX_4E068C5DA76ED395 (user_id), INDEX IDX_4E068C5D847B7245 (authorization_code_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oauth_authorization_code ADD CONSTRAINT FK_793B081719EB6921 FOREIGN KEY (client_id) REFERENCES oauth_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_authorization_code ADD CONSTRAINT FK_793B0817A76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_grant ADD CONSTRAINT FK_4E068C5D19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_grant ADD CONSTRAINT FK_4E068C5DA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth_grant ADD CONSTRAINT FK_4E068C5D847B7245 FOREIGN KEY (authorization_code_id) REFERENCES oauth_authorization_code (id) ON DELETE SET NULL');
        $this->addSql('DROP TABLE agent_token');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE agent_token (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(60) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, token_hash VARCHAR(64) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', revoked_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_669989435E237E06 (name), UNIQUE INDEX UNIQ_66998943B3BC57DA (token_hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oauth_authorization_code DROP FOREIGN KEY FK_793B081719EB6921');
        $this->addSql('ALTER TABLE oauth_authorization_code DROP FOREIGN KEY FK_793B0817A76ED395');
        $this->addSql('ALTER TABLE oauth_grant DROP FOREIGN KEY FK_4E068C5D19EB6921');
        $this->addSql('ALTER TABLE oauth_grant DROP FOREIGN KEY FK_4E068C5DA76ED395');
        $this->addSql('ALTER TABLE oauth_grant DROP FOREIGN KEY FK_4E068C5D847B7245');
        $this->addSql('DROP TABLE oauth_authorization_code');
        $this->addSql('DROP TABLE oauth_client');
        $this->addSql('DROP TABLE oauth_grant');
    }
}
