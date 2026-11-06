
CREATE TABLE `acces_cours` (
  `page_content_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`page_content_id`,`user_id`),
  KEY `IDX_26585FCC8F409273` (`page_content_id`),
  KEY `IDX_26585FCCA76ED395` (`user_id`),
  CONSTRAINT `FK_26585FCC8F409273` FOREIGN KEY (`page_content_id`) REFERENCES `appy_PageContent` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_26585FCCA76ED395` FOREIGN KEY (`user_id`) REFERENCES `appy_User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `acces_parcours` (
  `category_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`category_id`,`user_id`),
  KEY `IDX_4119943C12469DE2` (`category_id`),
  KEY `IDX_4119943CA76ED395` (`user_id`),
  CONSTRAINT `FK_4119943C12469DE2` FOREIGN KEY (`category_id`) REFERENCES `appy_Category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4119943CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `appy_User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `acces_qcm` (
  `qcm_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`qcm_id`,`user_id`),
  KEY `IDX_14EE4C8DFF6241A6` (`qcm_id`),
  KEY `IDX_14EE4C8DA76ED395` (`user_id`),
  CONSTRAINT `FK_14EE4C8DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `appy_User` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_14EE4C8DFF6241A6` FOREIGN KEY (`qcm_id`) REFERENCES `appy_QCM` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_Category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `visible` tinyint(1) DEFAULT '1',
  `positionMenus_id` int DEFAULT NULL,
  `superMenu_id` int DEFAULT NULL,
  `niveauCours_id` int DEFAULT NULL,
  `audience` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre` int DEFAULT NULL,
  `illustration_id` int DEFAULT NULL,
  `reserve` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_3BD61255ACB895C` (`positionMenus_id`),
  KEY `IDX_3BD6125537BAB3B4` (`superMenu_id`),
  KEY `IDX_3BD6125593E541ED` (`niveauCours_id`),
  KEY `IDX_3BD612555926566C` (`illustration_id`),
  CONSTRAINT `FK_3BD6125537BAB3B4` FOREIGN KEY (`superMenu_id`) REFERENCES `appy_SuperMenu` (`id`),
  CONSTRAINT `FK_3BD612555926566C` FOREIGN KEY (`illustration_id`) REFERENCES `course_media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_3BD6125593E541ED` FOREIGN KEY (`niveauCours_id`) REFERENCES `appy_NiveauCours` (`id`),
  CONSTRAINT `FK_3BD61255ACB895C` FOREIGN KEY (`positionMenus_id`) REFERENCES `appy_PositionMenus` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_ChoicesQCM` (
  `id` int NOT NULL AUTO_INCREMENT,
  `qcm_id` int NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isCorrect` tinyint(1) NOT NULL,
  `explication` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_B3840B43FF6241A6` (`qcm_id`),
  CONSTRAINT `FK_B3840B43FF6241A6` FOREIGN KEY (`qcm_id`) REFERENCES `appy_QCM` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_LanguageQCM` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_Logo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1EBF2ED012469DE2` (`category_id`),
  CONSTRAINT `FK_1EBF2ED012469DE2` FOREIGN KEY (`category_id`) REFERENCES `appy_Category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_Menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `positionMenus_id` int DEFAULT NULL,
  `niveauCours_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_70E2AA7112469DE2` (`category_id`),
  KEY `IDX_70E2AA71ACB895C` (`positionMenus_id`),
  KEY `IDX_70E2AA7193E541ED` (`niveauCours_id`),
  CONSTRAINT `FK_70E2AA7112469DE2` FOREIGN KEY (`category_id`) REFERENCES `appy_Category` (`id`),
  CONSTRAINT `FK_70E2AA7193E541ED` FOREIGN KEY (`niveauCours_id`) REFERENCES `appy_NiveauCours` (`id`),
  CONSTRAINT `FK_70E2AA71ACB895C` FOREIGN KEY (`positionMenus_id`) REFERENCES `appy_PositionMenus` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_NiveauCours` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_NiveauQCM` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_Page` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menus_id` int DEFAULT NULL,
  `seo_id` int DEFAULT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EE3B02E397E3DD86` (`seo_id`),
  KEY `IDX_EE3B02E314041B84` (`menus_id`),
  CONSTRAINT `FK_EE3B02E314041B84` FOREIGN KEY (`menus_id`) REFERENCES `appy_Menus` (`id`),
  CONSTRAINT `FK_EE3B02E397E3DD86` FOREIGN KEY (`seo_id`) REFERENCES `appy_Seo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_PageBlock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `code` longtext COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pageContent_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6F6419B1BD8FBEEF` (`pageContent_id`),
  CONSTRAINT `FK_6F6419B1BD8FBEEF` FOREIGN KEY (`pageContent_id`) REFERENCES `appy_PageContent` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_PageContent` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `menu_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `code` longtext COLLATE utf8mb4_unicode_ci,
  `duration` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `niveauCours_id` int DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'brouillon',
  `source_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_at` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
  `author` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `review_report` json DEFAULT NULL,
  `reviewed_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `ordre` int DEFAULT NULL,
  `reserve` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_AEA9AB28C4663E4` (`page_id`),
  KEY `IDX_AEA9AB2812469DE2` (`category_id`),
  KEY `IDX_AEA9AB28CCD7E912` (`menu_id`),
  KEY `IDX_AEA9AB2893E541ED` (`niveauCours_id`),
  CONSTRAINT `FK_AEA9AB2812469DE2` FOREIGN KEY (`category_id`) REFERENCES `appy_Category` (`id`),
  CONSTRAINT `FK_AEA9AB2893E541ED` FOREIGN KEY (`niveauCours_id`) REFERENCES `appy_NiveauCours` (`id`),
  CONSTRAINT `FK_AEA9AB28C4663E4` FOREIGN KEY (`page_id`) REFERENCES `appy_Page` (`id`),
  CONSTRAINT `FK_AEA9AB28CCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `appy_Menus` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_PositionMenus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_QCM` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `solution` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `languageQCM_id` int DEFAULT NULL,
  `niveauQCM_id` int DEFAULT NULL,
  `reserve` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_47C25A806C5D2C67` (`languageQCM_id`),
  KEY `IDX_47C25A80F662DA50` (`niveauQCM_id`),
  CONSTRAINT `FK_47C25A806C5D2C67` FOREIGN KEY (`languageQCM_id`) REFERENCES `appy_LanguageQCM` (`id`),
  CONSTRAINT `FK_47C25A80F662DA50` FOREIGN KEY (`niveauQCM_id`) REFERENCES `appy_NiveauQCM` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_Seo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metaDescription` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `metaKeywords` longtext COLLATE utf8mb4_unicode_ci,
  `ogTitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ogDescription` longtext COLLATE utf8mb4_unicode_ci,
  `ogImage` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonicalUrl` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `noIndex` tinyint(1) NOT NULL DEFAULT '0',
  `noFollow` tinyint(1) NOT NULL DEFAULT '0',
  `page` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `structuredData` longtext COLLATE utf8mb4_unicode_ci,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_52F84C2E140AB620` (`page`),
  UNIQUE KEY `UNIQ_52F84C2E12469DE2` (`category_id`),
  CONSTRAINT `FK_52F84C2E12469DE2` FOREIGN KEY (`category_id`) REFERENCES `appy_Category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_SiteConfiguration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `configKey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` json NOT NULL,
  `updatedAt` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_SuperMenu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `appy_User` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_USERNAME` (`username`),
  UNIQUE KEY `UNIQ_77A2628AE7927C74` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `course_media` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `width` int DEFAULT NULL,
  `height` int DEFAULT NULL,
  `checksum` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_text` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `kind` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'capture',
  `service` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `captured_at` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
  `author` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_review_at` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8387A6E43C0BE965` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `oauth_authorization_code` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `user_id` int NOT NULL,
  `code_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect_uri` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_challenge` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `used_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_793B0817E7530879` (`code_hash`),
  KEY `IDX_793B081719EB6921` (`client_id`),
  KEY `IDX_793B0817A76ED395` (`user_id`),
  CONSTRAINT `FK_793B081719EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_client` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_793B0817A76ED395` FOREIGN KEY (`user_id`) REFERENCES `appy_User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `oauth_client` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect_uris` json NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `last_used_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AD73274D19EB6921` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `oauth_grant` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `user_id` int NOT NULL,
  `authorization_code_id` int DEFAULT NULL,
  `access_token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `refresh_token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refresh_expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `revoked_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4E068C5D9982CF5B` (`access_token_hash`),
  UNIQUE KEY `UNIQ_4E068C5D7288F9EC` (`refresh_token_hash`),
  KEY `IDX_4E068C5D19EB6921` (`client_id`),
  KEY `IDX_4E068C5DA76ED395` (`user_id`),
  KEY `IDX_4E068C5D847B7245` (`authorization_code_id`),
  CONSTRAINT `FK_4E068C5D19EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_client` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4E068C5D847B7245` FOREIGN KEY (`authorization_code_id`) REFERENCES `oauth_authorization_code` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_4E068C5DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `appy_User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `password_reset_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6B7BA4B6B3BC57DA` (`token_hash`),
  KEY `IDX_6B7BA4B6A76ED395` (`user_id`),
  CONSTRAINT `FK_6B7BA4B6A76ED395` FOREIGN KEY (`user_id`) REFERENCES `appy_User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `qcm_draft` (
  `id` int NOT NULL AUTO_INCREMENT,
  `theme_id` int DEFAULT NULL,
  `level_id` int DEFAULT NULL,
  `published_qcm_id` int DEFAULT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `explanation` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `answers` json NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `review_report` json DEFAULT NULL,
  `reviewed_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `published_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_C85292EE59027487` (`theme_id`),
  KEY `IDX_C85292EE5FB14BA7` (`level_id`),
  KEY `IDX_C85292EE369F8154` (`published_qcm_id`),
  CONSTRAINT `FK_C85292EE369F8154` FOREIGN KEY (`published_qcm_id`) REFERENCES `appy_QCM` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C85292EE59027487` FOREIGN KEY (`theme_id`) REFERENCES `appy_LanguageQCM` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C85292EE5FB14BA7` FOREIGN KEY (`level_id`) REFERENCES `appy_NiveauQCM` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


