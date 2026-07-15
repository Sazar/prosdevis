-- Migration 004 — Table des relances de factures
-- À exécuter après le schéma principal

CREATE TABLE IF NOT EXISTS `invoice_reminders` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_id`    INT UNSIGNED NOT NULL,
    `company_id`    INT UNSIGNED NOT NULL,
    `level`         TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=douce, 2=ferme, 3=mise en demeure',
    `sent_to`       VARCHAR(255)  NOT NULL,
    `sent`          TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1=email envoyé avec succès',
    `triggered_by`  INT UNSIGNED DEFAULT NULL COMMENT 'NULL si automatique (cron)',
    `sent_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_reminders_invoice ON `invoice_reminders`(`invoice_id`);
CREATE INDEX idx_reminders_company ON `invoice_reminders`(`company_id`);
