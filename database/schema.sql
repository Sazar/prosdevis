-- ==========================================
-- ProsDevis - Schéma de base de données
-- MySQL 8.0+
-- ==========================================

CREATE DATABASE IF NOT EXISTS `prosdevis` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `prosdevis`;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------
-- ENTREPRISES (multi-tenant)
-- ------------------------------------------
CREATE TABLE `companies` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`            VARCHAR(255) NOT NULL,
    `siret`           VARCHAR(14) NOT NULL,
    `rcs`             VARCHAR(100) DEFAULT NULL,
    `vat_number`      VARCHAR(30)  DEFAULT NULL COMMENT 'Numéro TVA intracom',
    `address`         TEXT NOT NULL,
    `zip`             VARCHAR(20)  NOT NULL,
    `city`            VARCHAR(100) NOT NULL,
    `country`         VARCHAR(2)   NOT NULL DEFAULT 'FR',
    `phone`           VARCHAR(30)  DEFAULT NULL,
    `email`           VARCHAR(255) NOT NULL,
    `website`         VARCHAR(255) DEFAULT NULL,
    `logo_path`       VARCHAR(500) DEFAULT NULL,
    `primary_color`   VARCHAR(7)   DEFAULT '#2563eb',
    `secondary_color` VARCHAR(7)   DEFAULT '#1e40af',
    `font_family`     VARCHAR(100) DEFAULT 'Inter, sans-serif',
    `legal_mentions`  TEXT         DEFAULT NULL COMMENT 'Mentions légales supplémentaires',
    `payment_terms`   TEXT         DEFAULT NULL COMMENT 'Conditions de paiement par défaut',
    `plan`            ENUM('free','pro','enterprise') NOT NULL DEFAULT 'free',
    `plan_expires_at` TIMESTAMP    NULL DEFAULT NULL,
    `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------
-- UTILISATEURS
-- ------------------------------------------
CREATE TABLE `users` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`        INT UNSIGNED NOT NULL,
    `email`             VARCHAR(255) NOT NULL UNIQUE,
    `password`          VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
    `first_name`        VARCHAR(100) NOT NULL,
    `last_name`         VARCHAR(100) NOT NULL,
    `role`              ENUM('admin','collaborator','accountant') NOT NULL DEFAULT 'collaborator',
    `avatar_path`       VARCHAR(500) DEFAULT NULL,
    `phone`             VARCHAR(30)  DEFAULT NULL,
    `totp_secret`       VARCHAR(32)  DEFAULT NULL COMMENT '2FA TOTP secret',
    `totp_enabled`      TINYINT(1)   NOT NULL DEFAULT 0,
    `email_verified`    TINYINT(1)   NOT NULL DEFAULT 0,
    `email_token`       VARCHAR(64)  DEFAULT NULL,
    `reset_token`       VARCHAR(64)  DEFAULT NULL,
    `reset_expires_at`  TIMESTAMP    NULL DEFAULT NULL,
    `login_attempts`    TINYINT      NOT NULL DEFAULT 0,
    `locked_until`      TIMESTAMP    NULL DEFAULT NULL,
    `last_login_at`     TIMESTAMP    NULL DEFAULT NULL,
    `last_login_ip`     VARCHAR(45)  DEFAULT NULL,
    `remember_token`    VARCHAR(64)  DEFAULT NULL,
    `theme`             ENUM('light','dark','auto') NOT NULL DEFAULT 'auto',
    `gdpr_consent`      TINYINT(1)   NOT NULL DEFAULT 0,
    `gdpr_consent_at`   TIMESTAMP    NULL DEFAULT NULL,
    `deleted_at`        TIMESTAMP    NULL DEFAULT NULL COMMENT 'Soft delete RGPD',
    `is_active`         TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_users_email ON `users`(`email`);
CREATE INDEX idx_users_company ON `users`(`company_id`);

-- ------------------------------------------
-- SESSIONS
-- ------------------------------------------
CREATE TABLE `sessions` (
    `id`         VARCHAR(64) PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `ip`         VARCHAR(45)  NOT NULL,
    `user_agent` TEXT,
    `payload`    TEXT,
    `expires_at` TIMESTAMP    NOT NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------
-- CLIENTS
-- ------------------------------------------
CREATE TABLE `clients` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`    INT UNSIGNED NOT NULL,
    `type`          ENUM('individual','company') NOT NULL DEFAULT 'company',
    `name`          VARCHAR(255) NOT NULL COMMENT 'Raison sociale ou nom complet',
    `siret`         VARCHAR(14)  DEFAULT NULL,
    `vat_number`    VARCHAR(30)  DEFAULT NULL,
    `contact_name`  VARCHAR(255) DEFAULT NULL,
    `email`         VARCHAR(255) NOT NULL,
    `phone`         VARCHAR(30)  DEFAULT NULL,
    `address`       TEXT NOT NULL,
    `zip`           VARCHAR(20)  NOT NULL,
    `city`          VARCHAR(100) NOT NULL,
    `country`       VARCHAR(2)   NOT NULL DEFAULT 'FR',
    `notes`         TEXT         DEFAULT NULL,
    `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------
-- PRODUITS / SERVICES (catalogue)
-- ------------------------------------------
CREATE TABLE `products` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`  INT UNSIGNED NOT NULL,
    `reference`   VARCHAR(100) DEFAULT NULL,
    `name`        VARCHAR(255) NOT NULL,
    `description` TEXT         DEFAULT NULL,
    `unit`        VARCHAR(50)  DEFAULT 'forfait',
    `unit_price`  DECIMAL(12,4) NOT NULL DEFAULT 0,
    `vat_rate`    DECIMAL(5,2)  NOT NULL DEFAULT 20.00,
    `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------
-- TAUX DE TVA
-- ------------------------------------------
CREATE TABLE `vat_rates` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `country`     VARCHAR(2)   NOT NULL,
    `label`       VARCHAR(100) NOT NULL,
    `rate`        DECIMAL(5,2) NOT NULL,
    `is_default`  TINYINT(1)   NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `vat_rates` (`country`, `label`, `rate`, `is_default`) VALUES
('FR', 'TVA 20% (taux normal)', 20.00, 1),
('FR', 'TVA 10% (taux intermédiaire)', 10.00, 0),
('FR', 'TVA 5.5% (taux réduit)', 5.50, 0),
('FR', 'TVA 2.1% (taux super réduit)', 2.10, 0),
('FR', 'Exonéré (0%)', 0.00, 0),
('DE', 'MwSt 19% (Normalsatz)', 19.00, 0),
('DE', 'MwSt 7% (ermäßigter Satz)', 7.00, 0),
('BE', 'TVA 21% (taux normal)', 21.00, 0),
('BE', 'TVA 6% (taux réduit)', 6.00, 0),
('CH', 'TVA 8.1% (taux normal)', 8.10, 0),
('ES', 'IVA 21%', 21.00, 0),
('IT', 'IVA 22%', 22.00, 0);

-- ------------------------------------------
-- DEVIS
-- ------------------------------------------
CREATE TABLE `quotes` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`        INT UNSIGNED NOT NULL,
    `client_id`         INT UNSIGNED NOT NULL,
    `created_by`        INT UNSIGNED NOT NULL,
    `number`            VARCHAR(30)  NOT NULL UNIQUE COMMENT 'Ex: DEV-2026-0001',
    `status`            ENUM('draft','sent','viewed','accepted','refused','expired','converted') NOT NULL DEFAULT 'draft',
    `title`             VARCHAR(500) NOT NULL,
    `description`       TEXT         DEFAULT NULL,
    `issue_date`        DATE         NOT NULL,
    `validity_date`     DATE         NOT NULL,
    `currency`          VARCHAR(3)   NOT NULL DEFAULT 'EUR',
    `country_vat`       VARCHAR(2)   NOT NULL DEFAULT 'FR',
    `discount_type`     ENUM('percent','fixed') DEFAULT NULL,
    `discount_value`    DECIMAL(12,4) DEFAULT 0,
    `deposit_percent`   DECIMAL(5,2)  DEFAULT 0 COMMENT 'Acompte en %',
    `subtotal_ht`       DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_discount`    DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_vat`         DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_ttc`         DECIMAL(14,4) NOT NULL DEFAULT 0,
    `template_id`       INT UNSIGNED  DEFAULT NULL,
    `notes`             TEXT          DEFAULT NULL,
    `internal_notes`    TEXT          DEFAULT NULL,
    `payment_terms`     TEXT          DEFAULT NULL,
    `pdf_path`          VARCHAR(500)  DEFAULT NULL,
    `sent_at`           TIMESTAMP     NULL DEFAULT NULL,
    `viewed_at`         TIMESTAMP     NULL DEFAULT NULL,
    `accepted_at`       TIMESTAMP     NULL DEFAULT NULL,
    `refused_at`        TIMESTAMP     NULL DEFAULT NULL,
    `expired_at`        TIMESTAMP     NULL DEFAULT NULL,
    `converted_to`      INT UNSIGNED  DEFAULT NULL COMMENT 'ID de la facture générée',
    `signature_status`  ENUM('none','pending','signed','declined') NOT NULL DEFAULT 'none',
    `signature_token`   VARCHAR(64)   DEFAULT NULL,
    `signature_data`    TEXT          DEFAULT NULL COMMENT 'JSON signature électronique',
    `signature_ip`      VARCHAR(45)   DEFAULT NULL,
    `signature_at`      TIMESTAMP     NULL DEFAULT NULL,
    `facturx_data`      TEXT          DEFAULT NULL COMMENT 'XML Factur-X embarqué',
    `autosave_at`       TIMESTAMP     NULL DEFAULT NULL,
    `created_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_quotes_company ON `quotes`(`company_id`);
CREATE INDEX idx_quotes_status ON `quotes`(`status`);
CREATE INDEX idx_quotes_number ON `quotes`(`number`);

-- ------------------------------------------
-- LIGNES DE DEVIS
-- ------------------------------------------
CREATE TABLE `quote_lines` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `quote_id`    INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED DEFAULT NULL,
    `position`    SMALLINT     NOT NULL DEFAULT 0 COMMENT 'Ordre drag & drop',
    `type`        ENUM('service','product','subtotal','text','section') NOT NULL DEFAULT 'service',
    `reference`   VARCHAR(100) DEFAULT NULL,
    `name`        VARCHAR(500) NOT NULL,
    `description` TEXT         DEFAULT NULL,
    `unit`        VARCHAR(50)  DEFAULT 'forfait',
    `quantity`    DECIMAL(12,4) NOT NULL DEFAULT 1,
    `unit_price`  DECIMAL(12,4) NOT NULL DEFAULT 0,
    `vat_rate`    DECIMAL(5,2)  NOT NULL DEFAULT 20.00,
    `discount_type`  ENUM('percent','fixed') DEFAULT NULL,
    `discount_value` DECIMAL(12,4) DEFAULT 0,
    `total_ht`    DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_ttc`   DECIMAL(14,4) NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------
-- FACTURES (générées depuis devis ou directement)
-- ------------------------------------------
CREATE TABLE `invoices` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`     INT UNSIGNED NOT NULL,
    `client_id`      INT UNSIGNED NOT NULL,
    `quote_id`       INT UNSIGNED DEFAULT NULL,
    `created_by`     INT UNSIGNED NOT NULL,
    `number`         VARCHAR(30)  NOT NULL UNIQUE COMMENT 'Ex: FAC-2026-0001',
    `status`         ENUM('draft','sent','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
    `title`          VARCHAR(500) NOT NULL,
    `issue_date`     DATE         NOT NULL,
    `due_date`       DATE         NOT NULL,
    `currency`       VARCHAR(3)   NOT NULL DEFAULT 'EUR',
    `subtotal_ht`    DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_discount` DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_vat`      DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_ttc`      DECIMAL(14,4) NOT NULL DEFAULT 0,
    `amount_paid`    DECIMAL(14,4) NOT NULL DEFAULT 0,
    `notes`          TEXT          DEFAULT NULL,
    `pdf_path`       VARCHAR(500)  DEFAULT NULL,
    `facturx_xml`    LONGTEXT      DEFAULT NULL,
    `sent_at`        TIMESTAMP     NULL DEFAULT NULL,
    `paid_at`        TIMESTAMP     NULL DEFAULT NULL,
    `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`),
    FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`id`) ON SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------
-- LIGNES DE FACTURE
-- ------------------------------------------
CREATE TABLE `invoice_lines` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_id`  INT UNSIGNED NOT NULL,
    `position`    SMALLINT     NOT NULL DEFAULT 0,
    `type`        ENUM('service','product','subtotal','text','section') NOT NULL DEFAULT 'service',
    `reference`   VARCHAR(100) DEFAULT NULL,
    `name`        VARCHAR(500) NOT NULL,
    `description` TEXT         DEFAULT NULL,
    `unit`        VARCHAR(50)  DEFAULT 'forfait',
    `quantity`    DECIMAL(12,4) NOT NULL DEFAULT 1,
    `unit_price`  DECIMAL(12,4) NOT NULL DEFAULT 0,
    `vat_rate`    DECIMAL(5,2)  NOT NULL DEFAULT 20.00,
    `discount_type`  ENUM('percent','fixed') DEFAULT NULL,
    `discount_value` DECIMAL(12,4) DEFAULT 0,
    `total_ht`    DECIMAL(14,4) NOT NULL DEFAULT 0,
    `total_ttc`   DECIMAL(14,4) NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------
-- HISTORIQUE / AUDIT LOG
-- ------------------------------------------
CREATE TABLE `activity_logs` (
    `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED DEFAULT NULL,
    `company_id`  INT UNSIGNED DEFAULT NULL,
    `action`      VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50)  DEFAULT NULL COMMENT 'quote, invoice, client...',
    `entity_id`   INT UNSIGNED DEFAULT NULL,
    `old_values`  JSON         DEFAULT NULL,
    `new_values`  JSON         DEFAULT NULL,
    `ip`          VARCHAR(45)  DEFAULT NULL,
    `user_agent`  TEXT         DEFAULT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------
-- TEMPLATES DE DEVIS
-- ------------------------------------------
CREATE TABLE `quote_templates` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`  INT UNSIGNED NOT NULL,
    `name`        VARCHAR(255) NOT NULL,
    `is_default`  TINYINT(1)  NOT NULL DEFAULT 0,
    `config`      JSON        NOT NULL COMMENT 'Logo, couleurs, polices, mise en page...',
    `created_at`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------
-- BLOG (SEO + Marketing)
-- ------------------------------------------
CREATE TABLE `blog_posts` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `author_id`    INT UNSIGNED DEFAULT NULL,
    `slug`         VARCHAR(255) NOT NULL UNIQUE,
    `title`        VARCHAR(500) NOT NULL,
    `excerpt`      TEXT         DEFAULT NULL,
    `content`      LONGTEXT     NOT NULL,
    `cover_image`  VARCHAR(500) DEFAULT NULL,
    `status`       ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `meta_title`   VARCHAR(255) DEFAULT NULL,
    `meta_desc`    VARCHAR(500) DEFAULT NULL,
    `og_image`     VARCHAR(500) DEFAULT NULL,
    `published_at` TIMESTAMP    NULL DEFAULT NULL,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------
-- COMPTEUR NUMEROTATION
-- ------------------------------------------
CREATE TABLE `number_sequences` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `company_id`  INT UNSIGNED NOT NULL,
    `type`        ENUM('quote','invoice') NOT NULL,
    `year`        YEAR         NOT NULL,
    `last_number` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `unique_seq` (`company_id`, `type`, `year`),
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
