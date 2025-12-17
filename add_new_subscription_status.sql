-- Migration: Add 'new' status to subscriptions table
-- Date: 2025-11-19
-- Description: Adds 'new' status option to subscriptions.status enum field
--              This status is used for merchants who sign up for plans without trials
--              and need to complete payment before their subscription becomes active.

-- Add 'new' to the status enum
ALTER TABLE `subscriptions`
MODIFY COLUMN `status` enum('trial','active','past_due','cancelled','expired','new') NOT NULL;

-- Update any existing subscriptions with status that might need to be set to 'new'
-- This is optional and depends on your current data state
-- If you have subscriptions that are not active and not in trial, you may want to review them manually

-- Optional: Log the change
-- SELECT CONCAT('Migration completed: Added "new" status to subscriptions table at ', NOW()) as migration_log;
