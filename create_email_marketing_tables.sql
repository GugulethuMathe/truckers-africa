-- Email Marketing System Database Tables
-- Run this SQL script to create the email marketing lead management tables

-- Table 1: Email Marketing Leads
CREATE TABLE `email_marketing_leads` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) DEFAULT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone_number` VARCHAR(50) DEFAULT NULL,
    `company_name` VARCHAR(255) DEFAULT NULL,
    `lead_source` VARCHAR(100) DEFAULT NULL COMMENT 'Source of the lead (e.g., website, referral, import)',
    `lead_status` ENUM('new', 'contacted', 'qualified', 'converted', 'unsubscribed') NOT NULL DEFAULT 'new',
    `country` VARCHAR(100) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `tags` TEXT DEFAULT NULL COMMENT 'Comma-separated tags for categorizing leads',
    `email_sent_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Number of marketing emails sent to this lead',
    `last_email_sent_at` DATETIME DEFAULT NULL,
    `last_opened_at` DATETIME DEFAULT NULL COMMENT 'Last time lead opened a marketing email',
    `last_clicked_at` DATETIME DEFAULT NULL COMMENT 'Last time lead clicked a link in marketing email',
    `is_subscribed` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = subscribed, 0 = unsubscribed',
    `unsubscribed_at` DATETIME DEFAULT NULL,
    `added_by` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Admin user ID who added this lead',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `email` (`email`),
    KEY `lead_status` (`lead_status`),
    KEY `is_subscribed` (`is_subscribed`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table 2: Email Campaigns
CREATE TABLE `email_campaigns` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_name` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `email_content` LONGTEXT NOT NULL,
    `status` ENUM('draft', 'scheduled', 'sending', 'sent', 'failed') NOT NULL DEFAULT 'draft',
    `scheduled_at` DATETIME DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `total_recipients` INT(11) NOT NULL DEFAULT 0,
    `total_sent` INT(11) NOT NULL DEFAULT 0,
    `total_opened` INT(11) NOT NULL DEFAULT 0,
    `total_clicked` INT(11) NOT NULL DEFAULT 0,
    `total_failed` INT(11) NOT NULL DEFAULT 0,
    `created_by` INT(11) UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `status` (`status`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table 3: Email Campaign Leads (Junction Table)
CREATE TABLE `email_campaign_leads` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` INT(11) UNSIGNED NOT NULL,
    `lead_id` INT(11) UNSIGNED NOT NULL,
    `email_status` ENUM('pending', 'sent', 'failed', 'bounced') NOT NULL DEFAULT 'pending',
    `sent_at` DATETIME DEFAULT NULL,
    `opened_at` DATETIME DEFAULT NULL,
    `clicked_at` DATETIME DEFAULT NULL,
    `failure_reason` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `campaign_id` (`campaign_id`),
    KEY `lead_id` (`lead_id`),
    KEY `email_status` (`email_status`),
    CONSTRAINT `email_campaign_leads_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `email_campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `email_campaign_leads_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `email_marketing_leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
