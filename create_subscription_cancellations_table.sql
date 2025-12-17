-- Migration: Create subscription_cancellations table
-- Date: 2025-11-19
-- Description: Creates table to store cancellation feedback from merchants
--              Tracks reasons and comments when merchants cancel their subscriptions

CREATE TABLE IF NOT EXISTS `subscription_cancellations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subscription_id` int(10) UNSIGNED NOT NULL,
  `merchant_id` int(10) UNSIGNED NOT NULL,
  `plan_id` int(10) UNSIGNED NOT NULL,
  `cancellation_reason` varchar(100) NOT NULL,
  `cancellation_comments` text DEFAULT NULL,
  `cancelled_at` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `merchant_id` (`merchant_id`),
  KEY `cancelled_at` (`cancelled_at`),
  CONSTRAINT `subscription_cancellations_subscription_id_foreign` 
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `subscription_cancellations_merchant_id_foreign` 
    FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `subscription_cancellations_plan_id_foreign`
    FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Stores merchant subscription cancellation feedback';

-- Verify table was created
SELECT 'Table subscription_cancellations created successfully!' as status;

