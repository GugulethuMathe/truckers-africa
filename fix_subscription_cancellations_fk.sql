-- Fix foreign key constraint in subscription_cancellations table
-- The foreign key was incorrectly referencing 'subscription_plans' instead of 'plans'
--
-- INSTRUCTIONS:
-- 1. Open phpMyAdmin
-- 2. Select database 'app_truckers_africa' from the left sidebar
-- 3. Click "SQL" tab
-- 4. Copy and paste the commands below (WITHOUT the USE statement)
-- 5. Click "Go"

-- Drop the incorrect foreign key constraint
ALTER TABLE `subscription_cancellations`
DROP FOREIGN KEY `subscription_cancellations_plan_id_foreign`;

-- Add the correct foreign key constraint referencing 'plans' table
ALTER TABLE `subscription_cancellations`
ADD CONSTRAINT `subscription_cancellations_plan_id_foreign`
FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
ON DELETE CASCADE
ON UPDATE CASCADE;

