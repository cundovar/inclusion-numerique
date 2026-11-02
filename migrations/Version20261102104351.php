<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Base partagée avec mediateur (inclusion-numerique, egflbugcundo6) : ajoute seulement ce qui manque.
 *
 * Chaque table, colonne, index et clé étrangère est vérifié avant d'être créé : sur une base DevDoc
 * déjà complète, seuls les défauts et les deux colonnes de mediateur (`email`, `audience`) changent.
 * Rien n'est supprimé, et les colonnes ajoutées aux tables de mediateur ont une valeur par défaut
 * pour que son code continue d'écrire sans les connaître.
 */
final class Version20261102104351 extends AbstractMigration
{
    /** Tables DevDoc absentes du schéma de mediateur */
    private const TABLES = [
        'appy_ExoContent' => "CREATE TABLE appy_ExoContent (id INT AUTO_INCREMENT NOT NULL, exo_id INT DEFAULT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(50) DEFAULT NULL, content LONGTEXT DEFAULT NULL, code LONGTEXT DEFAULT NULL, exoMenu_id INT DEFAULT NULL, INDEX IDX_B81F09DFDA1C6F33 (exo_id), INDEX IDX_B81F09DF12469DE2 (category_id), INDEX IDX_B81F09DF8F27B68 (exoMenu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'appy_UserCustomization' => "CREATE TABLE appy_UserCustomization (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, settings JSON NOT NULL, createdAt DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updatedAt DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_FBA50EFDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'revision' => "CREATE TABLE revision (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, type_revision VARCHAR(50) NOT NULL, commentaire LONGTEXT DEFAULT NULL, ancien_code LONGTEXT DEFAULT NULL, nouveau_code LONGTEXT DEFAULT NULL, date_revision DATETIME NOT NULL, appliquee TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_6D6315CC591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'appy_ExoMenu' => "CREATE TABLE appy_ExoMenu (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, label VARCHAR(100) DEFAULT NULL, INDEX IDX_61CDAD812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'appy_favorite' => "CREATE TABLE appy_favorite (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, page_id INT NOT NULL, createdAt DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_AC29E71BA76ED395 (user_id), INDEX IDX_AC29E71BC4663E4 (page_id), UNIQUE INDEX user_page_unique (user_id, page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'appy_Note' => "CREATE TABLE appy_Note (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, page_id INT NOT NULL, content LONGTEXT NOT NULL, createdAt DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updatedAt DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_358C4ED7A76ED395 (user_id), INDEX IDX_358C4ED7C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'diaporama' => "CREATE TABLE diaporama (id INT AUTO_INCREMENT NOT NULL, cours_id INT DEFAULT NULL, chapitre_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, sous_titre VARCHAR(255) NOT NULL, diapositives JSON NOT NULL, status VARCHAR(20) NOT NULL, author VARCHAR(100) NOT NULL, empreinte_source VARCHAR(64) DEFAULT NULL, review_report JSON DEFAULT NULL, reviewed_by VARCHAR(100) DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', published_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_776658BE7ECF78B0 (cours_id), INDEX IDX_776658BE1FBEEF7B (chapitre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'appy_UserPageVisit' => "CREATE TABLE appy_UserPageVisit (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, page_url VARCHAR(255) NOT NULL, page_title VARCHAR(255) DEFAULT NULL, visited_at DATETIME NOT NULL, user_agent VARCHAR(255) DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, time_spent INT DEFAULT NULL, INDEX IDX_7D09FA76A76ED395 (user_id), INDEX IDX_7D09FA76A76ED395EDA764E3 (user_id, visited_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'appy_DocDeCode' => "CREATE TABLE appy_DocDeCode (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'agent_course_generation' => "CREATE TABLE agent_course_generation (id INT AUTO_INCREMENT NOT NULL, course_id INT DEFAULT NULL, batch_id VARCHAR(100) NOT NULL, external_id VARCHAR(100) NOT NULL, status VARCHAR(20) NOT NULL, verification_attempts INT NOT NULL, payload JSON NOT NULL, candidate JSON DEFAULT NULL, verification_report JSON DEFAULT NULL, technical_error LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', finished_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_27DEF7CB591CC992 (course_id), UNIQUE INDEX generation_batch_item_unique (batch_id, external_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'appy_Exo' => "CREATE TABLE appy_Exo (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(100) NOT NULL, exoMenu_id INT DEFAULT NULL, INDEX IDX_B53FFFF08F27B68 (exoMenu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'rag_passage' => "CREATE TABLE rag_passage (id INT AUTO_INCREMENT NOT NULL, page_content_id INT NOT NULL, position INT NOT NULL, content LONGTEXT NOT NULL, checksum VARCHAR(64) NOT NULL, INDEX IDX_RAG_PASSAGE_COURSE (page_content_id), FULLTEXT INDEX rag_passage_content_fulltext (content), UNIQUE INDEX uniq_rag_course_position (page_content_id, position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'appy_ExoBlock' => "CREATE TABLE appy_ExoBlock (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT DEFAULT NULL, code LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, exoContent_id INT DEFAULT NULL, INDEX IDX_626E16DAD5B57270 (exoContent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
        'appy_PropositionIA' => "CREATE TABLE appy_PropositionIA (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(50) NOT NULL, statut VARCHAR(20) NOT NULL, payload JSON DEFAULT NULL, createdAt DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB",
    ];

    /** [table, colonne, SQL] : une base DevDoc peut déjà avoir la clé sous un autre nom */
    private const FOREIGN_KEYS = [
        ['appy_ExoContent', 'exo_id', "ALTER TABLE appy_ExoContent ADD CONSTRAINT FK_B81F09DFDA1C6F33 FOREIGN KEY (exo_id) REFERENCES appy_Exo (id)"],
        ['appy_ExoContent', 'category_id', "ALTER TABLE appy_ExoContent ADD CONSTRAINT FK_B81F09DF12469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)"],
        ['appy_ExoContent', 'exoMenu_id', "ALTER TABLE appy_ExoContent ADD CONSTRAINT FK_B81F09DF8F27B68 FOREIGN KEY (exoMenu_id) REFERENCES appy_ExoMenu (id)"],
        ['appy_UserCustomization', 'user_id', "ALTER TABLE appy_UserCustomization ADD CONSTRAINT FK_FBA50EFDA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id)"],
        ['revision', 'course_id', "ALTER TABLE revision ADD CONSTRAINT FK_6D6315CC591CC992 FOREIGN KEY (course_id) REFERENCES appy_PageContent (id) ON DELETE CASCADE"],
        ['appy_ExoMenu', 'category_id', "ALTER TABLE appy_ExoMenu ADD CONSTRAINT FK_61CDAD812469DE2 FOREIGN KEY (category_id) REFERENCES appy_Category (id)"],
        ['appy_favorite', 'user_id', "ALTER TABLE appy_favorite ADD CONSTRAINT FK_AC29E71BA76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id) ON DELETE CASCADE"],
        ['appy_favorite', 'page_id', "ALTER TABLE appy_favorite ADD CONSTRAINT FK_AC29E71BC4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id) ON DELETE CASCADE"],
        ['appy_Note', 'user_id', "ALTER TABLE appy_Note ADD CONSTRAINT FK_358C4ED7A76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id)"],
        ['appy_Note', 'page_id', "ALTER TABLE appy_Note ADD CONSTRAINT FK_358C4ED7C4663E4 FOREIGN KEY (page_id) REFERENCES appy_Page (id)"],
        ['diaporama', 'cours_id', "ALTER TABLE diaporama ADD CONSTRAINT FK_776658BE7ECF78B0 FOREIGN KEY (cours_id) REFERENCES appy_PageContent (id) ON DELETE CASCADE"],
        ['diaporama', 'chapitre_id', "ALTER TABLE diaporama ADD CONSTRAINT FK_776658BE1FBEEF7B FOREIGN KEY (chapitre_id) REFERENCES appy_Menus (id) ON DELETE CASCADE"],
        ['appy_UserPageVisit', 'user_id', "ALTER TABLE appy_UserPageVisit ADD CONSTRAINT FK_7D09FA76A76ED395 FOREIGN KEY (user_id) REFERENCES `appy_User` (id)"],
        ['agent_course_generation', 'course_id', "ALTER TABLE agent_course_generation ADD CONSTRAINT FK_27DEF7CB591CC992 FOREIGN KEY (course_id) REFERENCES appy_PageContent (id) ON DELETE SET NULL"],
        ['appy_Exo', 'exoMenu_id', "ALTER TABLE appy_Exo ADD CONSTRAINT FK_B53FFFF08F27B68 FOREIGN KEY (exoMenu_id) REFERENCES appy_ExoMenu (id)"],
        ['rag_passage', 'page_content_id', "ALTER TABLE rag_passage ADD CONSTRAINT FK_F1A1688E8F409273 FOREIGN KEY (page_content_id) REFERENCES appy_PageContent (id) ON DELETE CASCADE"],
        ['appy_ExoBlock', 'exoContent_id', "ALTER TABLE appy_ExoBlock ADD CONSTRAINT FK_626E16DAD5B57270 FOREIGN KEY (exoContent_id) REFERENCES appy_ExoContent (id)"],
        ['course_media', 'course_id', "ALTER TABLE course_media ADD CONSTRAINT FK_8387A6E4591CC992 FOREIGN KEY (course_id) REFERENCES appy_PageContent (id) ON DELETE SET NULL"],
        ['course_media', 'generation_id', "ALTER TABLE course_media ADD CONSTRAINT FK_8387A6E4553A6EC4 FOREIGN KEY (generation_id) REFERENCES agent_course_generation (id) ON DELETE SET NULL"],
        ['appy_Logo', 'docDeCode_id', "ALTER TABLE appy_Logo ADD CONSTRAINT FK_1EBF2ED0C703A8B4 FOREIGN KEY (docDeCode_id) REFERENCES appy_DocDeCode (id)"],
    ];

    /** [table, colonne, SQL] */
    private const COLUMNS = [
        ['appy_PageContent', 'visible', 'ALTER TABLE appy_PageContent ADD visible TINYINT(1) DEFAULT 1'],
        ['appy_User', 'trackPageVisits', 'ALTER TABLE `appy_User` ADD trackPageVisits TINYINT(1) DEFAULT 1 NOT NULL'],
        ['appy_User', 'email', 'ALTER TABLE `appy_User` ADD email VARCHAR(180) DEFAULT NULL'],
        ['appy_Category', 'audience', 'ALTER TABLE appy_Category ADD audience VARCHAR(20) DEFAULT NULL'],
        ['appy_Logo', 'docDeCode_id', 'ALTER TABLE appy_Logo ADD docDeCode_id INT DEFAULT NULL'],
        ['appy_SuperMenu', 'ordre', 'ALTER TABLE appy_SuperMenu ADD ordre INT DEFAULT NULL'],
        ['course_media', 'course_id', 'ALTER TABLE course_media ADD course_id INT DEFAULT NULL'],
        ['course_media', 'generation_id', 'ALTER TABLE course_media ADD generation_id INT DEFAULT NULL'],
        ['course_media', 'prompt', 'ALTER TABLE course_media ADD prompt LONGTEXT DEFAULT NULL'],
    ];

    /** [table, colonne indexée, SQL] : même remarque pour les noms d'index */
    private const INDEXES = [
        ['appy_User', 'email', 'CREATE UNIQUE INDEX UNIQ_77A2628AE7927C74 ON `appy_User` (email)'],
        ['appy_Logo', 'docDeCode_id', 'CREATE UNIQUE INDEX UNIQ_1EBF2ED0C703A8B4 ON appy_Logo (docDeCode_id)'],
        ['course_media', 'course_id', 'CREATE INDEX IDX_8387A6E4591CC992 ON course_media (course_id)'],
        ['course_media', 'generation_id', 'CREATE INDEX IDX_8387A6E4553A6EC4 ON course_media (generation_id)'],
    ];

    /** Colonnes OAuth du connecteur : mediateur ne les écrit pas, d'où le défaut vide */
    private const OAUTH_COLUMNS = [
        'resource' => 'VARCHAR(500) DEFAULT \'\' NOT NULL',
        'scope' => 'VARCHAR(64) DEFAULT \'\' NOT NULL',
    ];

    public function getDescription(): string
    {
        return 'Base partagée avec mediateur : ajoute les tables et colonnes DevDoc manquantes, sans rien supprimer';
    }

    public function up(Schema $schema): void
    {
        foreach (self::TABLES as $table => $sql) {
            if (!$schema->hasTable($table)) {
                $this->addSql($sql);
            }
        }

        $addedVisible = false;
        foreach (self::COLUMNS as [$table, $column, $sql]) {
            if (!$schema->getTable($table)->hasColumn($column)) {
                $this->addSql($sql);
                $addedVisible = $addedVisible || $column === 'visible';
            }
        }
        // Les cours de mediateur n'ont que leur statut : `visible` le suit, comme dans PageContent::setVisible()
        if ($addedVisible) {
            $this->addSql("UPDATE appy_PageContent SET visible = (status = 'publie')");
        }

        foreach (['oauth_grant', 'oauth_authorization_code'] as $table) {
            foreach (self::OAUTH_COLUMNS as $column => $definition) {
                $verb = $schema->getTable($table)->hasColumn($column) ? sprintf('CHANGE %1$s %1$s', $column) : 'ADD '.$column;
                $this->addSql(sprintf('ALTER TABLE %s %s %s', $table, $verb, $definition));
            }
        }
        // Défaut SQL de mediateur ; le défaut PHP de PageContent reste « publie »
        $this->addSql("ALTER TABLE appy_PageContent CHANGE status status VARCHAR(20) DEFAULT 'brouillon' NOT NULL");

        foreach (self::INDEXES as [$table, $column, $sql]) {
            if (!$schema->getTable($table)->hasColumn($column) || !$schema->getTable($table)->columnsAreIndexed([$column])) {
                $this->addSql($sql);
            }
        }

        foreach (self::FOREIGN_KEYS as [$table, $column, $sql]) {
            if (!$this->hasForeignKeyOn($schema, $table, $column)) {
                $this->addSql($sql);
            }
        }
    }

    private function hasForeignKeyOn(Schema $schema, string $table, string $column): bool
    {
        if (!$schema->hasTable($table)) {
            return false;
        }
        foreach ($schema->getTable($table)->getForeignKeys() as $foreignKey) {
            if (array_map('strtolower', $foreignKey->getLocalColumns()) === [strtolower($column)]) {
                return true;
            }
        }

        return false;
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Base partagée avec mediateur : ne rien supprimer automatiquement.');
    }
}
