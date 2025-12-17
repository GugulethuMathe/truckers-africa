<?php
/**
 * Subscription Warning Banner Component
 *
 * Shows warning/error messages for subscription status
 * Usage: echo view('components/subscription_warning');
 */

helper('subscription');

// Get warning for merchant or branch user
$warning = null;

if (session()->get('merchant_id')) {
    $warning = get_subscription_warning();
} elseif (session()->get('branch_location_id')) {
    $warning = get_branch_subscription_warning();
}

if (!$warning) {
    return; // No warning to display
}

$bgColor = $warning['type'] === 'error' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200';
$textColor = $warning['type'] === 'error' ? 'text-red-800' : 'text-yellow-800';
$iconColor = $warning['type'] === 'error' ? 'text-red-600' : 'text-yellow-600';
?>

<div class="<?= $bgColor ?> border-l-4 p-4 mb-6 rounded-r-lg shadow-sm" role="alert">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <?php if ($warning['type'] === 'error'): ?>
                <!-- Error Icon -->
                <svg class="w-5 h-5 <?= $iconColor ?>" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            <?php else: ?>
                <!-- Warning Icon -->
                <svg class="w-5 h-5 <?= $iconColor ?>" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            <?php endif; ?>
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm <?= $textColor ?> font-medium">
                <?= $warning['message'] ?>
            </p>
        </div>
        <button type="button" class="ml-3 inline-flex flex-shrink-0 <?= $textColor ?> hover:opacity-75" onclick="this.parentElement.parentElement.remove()">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
</div>
