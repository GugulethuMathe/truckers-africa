-- Migration: Add 'trial_pending' status to subscriptions table
-- Date: 2025-11-19
-- Description: Adds 'trial_pending' status option to subscriptions.status enum field
--              This status is used for merchants who sign up for trial plans but haven't
--              provided their payment method yet. Once payment method is captured (R0.00 charge),
--              status changes to 'trial' and the trial period begins.

-- Add 'trial_pending' to the status enum
ALTER TABLE `subscriptions`
MODIFY COLUMN `status` enum('trial','active','past_due','cancelled','expired','new','trial_pending') NOT NULL;

-- Optional: Log the change
-- SELECT CONCAT('Migration completed: Added "trial_pending" status to subscriptions table at ', NOW()) as migration_log;
