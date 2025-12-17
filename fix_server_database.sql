-- Fix for server database - Remove and recreate views without DEFINER
-- Run this SQL script in phpMyAdmin on your server

-- Drop the problematic view
DROP VIEW IF EXISTS `merchant_plan_limits`;

-- Recreate the view without DEFINER clause
CREATE VIEW `merchant_plan_limits` AS
SELECT
    `m`.`id` AS `merchant_id`,
    `m`.`business_name` AS `business_name`,
    `s`.`id` AS `subscription_id`,
    `p`.`id` AS `plan_id`,
    `p`.`name` AS `plan_name`,
    MAX(CASE WHEN `pl`.`limitation_type` = 'max_locations' THEN `pl`.`limit_value` END) AS `max_locations`,
    MAX(CASE WHEN `pl`.`limitation_type` = 'max_listings' THEN `pl`.`limit_value` END) AS `max_listings`,
    MAX(CASE WHEN `pl`.`limitation_type` = 'max_categories' THEN `pl`.`limit_value` END) AS `max_categories`,
    MAX(CASE WHEN `pl`.`limitation_type` = 'max_gallery_images' THEN `pl`.`limit_value` END) AS `max_gallery_images`,
    `m`.`current_locations_count` AS `current_locations_count`,
    `m`.`current_listings_count` AS `current_listings_count`
FROM
    `merchants` `m`
    LEFT JOIN `subscriptions` `s` ON `s`.`merchant_id` = `m`.`id` AND `s`.`status` IN ('active','trial')
    LEFT JOIN `plans` `p` ON `p`.`id` = `s`.`plan_id`
    LEFT JOIN `plan_limitations` `pl` ON `pl`.`plan_id` = `p`.`id`
GROUP BY
    `m`.`id`,
    `m`.`business_name`,
    `s`.`id`,
    `p`.`id`,
    `p`.`name`,
    `m`.`current_locations_count`,
    `m`.`current_listings_count`;
