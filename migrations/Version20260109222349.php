<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260109222349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appy_niveaucours_appy_category DROP FOREIGN KEY FK_2B74B5069DEEA1B5');
        $this->addSql('ALTER TABLE appy_niveaucours_appy_category DROP FOREIGN KEY FK_2B74B506A89E4924');
        $this->addSql('ALTER TABLE appy_niveaucours_appy_menus DROP FOREIGN KEY FK_D062321AA89E4924');
        $this->addSql('ALTER TABLE appy_niveaucours_appy_menus DROP FOREIGN KEY FK_D062321AD0E87246');
        $this->addSql('ALTER TABLE appy_niveaucours_appy_pagecontent DROP FOREIGN KEY FK_D670EAA730DFD7A0');
        $this->addSql('ALTER TABLE appy_niveaucours_appy_pagecontent DROP FOREIGN KEY FK_D670EAA7A89E4924');
        $this->addSql('DROP TABLE appy_niveaucours_appy_category');
        $this->addSql('DROP TABLE appy_niveaucours_appy_menus');
        $this->addSql('DROP TABLE appy_niveaucours_appy_pagecontent');
        $this->addSql('ALTER TABLE appy_Category ADD niveauCours_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appy_Category ADD CONSTRAINT FK_3BD6125593E541ED FOREIGN KEY (niveauCours_id) REFERENCES appy_NiveauCours (id)');
        $this->addSql('CREATE INDEX IDX_3BD6125593E541ED ON appy_Category (niveauCours_id)');
        $this->addSql('ALTER TABLE appy_Menus ADD niveauCours_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appy_Menus ADD CONSTRAINT FK_70E2AA7193E541ED FOREIGN KEY (niveauCours_id) REFERENCES appy_NiveauCours (id)');
        $this->addSql('CREATE INDEX IDX_70E2AA7193E541ED ON appy_Menus (niveauCours_id)');
        $this->addSql('ALTER TABLE appy_PageContent ADD niveauCours_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appy_PageContent ADD CONSTRAINT FK_AEA9AB2893E541ED FOREIGN KEY (niveauCours_id) REFERENCES appy_NiveauCours (id)');
        $this->addSql('CREATE INDEX IDX_AEA9AB2893E541ED ON appy_PageContent (niveauCours_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appy_niveaucours_appy_category (appy_niveaucours_id INT NOT NULL, appy_category_id INT NOT NULL, INDEX IDX_2B74B5069DEEA1B5 (appy_category_id), INDEX IDX_2B74B506A89E4924 (appy_niveaucours_id), PRIMARY KEY(appy_niveaucours_id, appy_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE appy_niveaucours_appy_menus (appy_niveaucours_id INT NOT NULL, appy_menus_id INT NOT NULL, INDEX IDX_D062321AD0E87246 (appy_menus_id), INDEX IDX_D062321AA89E4924 (appy_niveaucours_id), PRIMARY KEY(appy_niveaucours_id, appy_menus_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE appy_niveaucours_appy_pagecontent (appy_niveaucours_id INT NOT NULL, appy_pagecontent_id INT NOT NULL, INDEX IDX_D670EAA730DFD7A0 (appy_pagecontent_id), INDEX IDX_D670EAA7A89E4924 (appy_niveaucours_id), PRIMARY KEY(appy_niveaucours_id, appy_pagecontent_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE appy_niveaucours_appy_category ADD CONSTRAINT FK_2B74B5069DEEA1B5 FOREIGN KEY (appy_category_id) REFERENCES appy_Category (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_niveaucours_appy_category ADD CONSTRAINT FK_2B74B506A89E4924 FOREIGN KEY (appy_niveaucours_id) REFERENCES appy_NiveauCours (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_niveaucours_appy_menus ADD CONSTRAINT FK_D062321AA89E4924 FOREIGN KEY (appy_niveaucours_id) REFERENCES appy_NiveauCours (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_niveaucours_appy_menus ADD CONSTRAINT FK_D062321AD0E87246 FOREIGN KEY (appy_menus_id) REFERENCES appy_Menus (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_niveaucours_appy_pagecontent ADD CONSTRAINT FK_D670EAA730DFD7A0 FOREIGN KEY (appy_pagecontent_id) REFERENCES appy_PageContent (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_niveaucours_appy_pagecontent ADD CONSTRAINT FK_D670EAA7A89E4924 FOREIGN KEY (appy_niveaucours_id) REFERENCES appy_NiveauCours (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appy_Category DROP FOREIGN KEY FK_3BD6125593E541ED');
        $this->addSql('DROP INDEX IDX_3BD6125593E541ED ON appy_Category');
        $this->addSql('ALTER TABLE appy_Category DROP niveauCours_id');
        $this->addSql('ALTER TABLE appy_Menus DROP FOREIGN KEY FK_70E2AA7193E541ED');
        $this->addSql('DROP INDEX IDX_70E2AA7193E541ED ON appy_Menus');
        $this->addSql('ALTER TABLE appy_Menus DROP niveauCours_id');
        $this->addSql('ALTER TABLE appy_PageContent DROP FOREIGN KEY FK_AEA9AB2893E541ED');
        $this->addSql('DROP INDEX IDX_AEA9AB2893E541ED ON appy_PageContent');
        $this->addSql('ALTER TABLE appy_PageContent DROP niveauCours_id');
    }
}
