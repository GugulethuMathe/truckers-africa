-- Script to fix existing subscriptions with incorrect status
-- Date: 2025-11-19
-- Description: Updates subscriptions that were created with old logic
--              Sets correct status based on plan type and payment completion

-- First, let's see what we're dealing with
SELECT
    s.id,
    s.merchant_id,
    s.plan_id,
    p.name as plan_name,
    p.price,
    p.trial_days,
    p.has_trial,
    s.status,
    s.payfast_token,
    s.trial_ends_at,
    s.current_period_starts_at,
    s.created_at
FROM subscriptions s
JOIN plans p ON s.plan_id = p.id
WHERE s.status IN ('trial', 'active')
ORDER BY s.created_at DESC;

-- Fix subscriptions with status 'trial' that should be 'trial_pending'
-- (have trial plan but no PayFast token = payment method not captured)
UPDATE subscriptions s
JOIN plans p ON s.plan_id = p.id
SET s.status = 'trial_pending',
    s.current_period_starts_at = NULL,
    s.current_period_ends_at = NULL
WHERE s.status = 'trial'
  AND p.has_trial = 1
  AND (s.payfast_token IS NULL OR s.payfast_token = '');

-- Fix subscriptions with status 'trial' that should be 'new'
-- (non-trial plan but no PayFast token = payment not completed)
UPDATE subscriptions s
JOIN plans p ON s.plan_id = p.id
SET s.status = 'new',
    s.trial_ends_at = NULL,
    s.current_period_starts_at = NULL,
    s.current_period_ends_at = NULL
WHERE s.status = 'trial'
  AND (p.has_trial = 0 OR p.trial_days = 0)
  AND (s.payfast_token IS NULL OR s.payfast_token = '');

-- Verify the changes
SELECT
    s.id,
    s.merchant_id,
    s.plan_id,
    p.name as plan_name,
    s.status,
    s.payfast_token,
    'FIXED' as note
FROM subscriptions s
JOIN plans p ON s.plan_id = p.id
WHERE s.status IN ('trial_pending', 'new', 'trial', 'active')
ORDER BY s.updated_at DESC
LIMIT 10;
