<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: <?= $status_color ?? '#000f25' ?>;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .alert-badge {
            background-color: <?= $badge_bg ?? '#e8f4f8' ?>;
            border-left: 4px solid <?= $badge_border ?? '#000f25' ?>;
            padding: 15px;
            margin: 20px 0;
            border-radius: 3px;
        }
        .subscription-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .detail-row {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #10b981;
            color: white;
        }
        .status-trial {
            background-color: #3b82f6;
            color: white;
        }
        .status-expired {
            background-color: #ef4444;
            color: white;
        }
        .status-cancelled {
            background-color: #f59e0b;
            color: white;
        }
        .status-past_due {
            background-color: #f59e0b;
            color: white;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 0.9em;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #000f25;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background-color: #e8f4f8;
            border: 1px solid #3b82f6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .success-box {
            background-color: #d1fae5;
            border: 1px solid #10b981;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= esc($email_icon ?? '🔔') ?> Subscription Status Update</h1>
    </div>

    <div class="content">
        <p>Dear <?= esc($business_name) ?>,</p>

        <div class="alert-badge">
            <h2 style="margin: 0; color: <?= esc($heading_color ?? '#000f25') ?>;"><?= esc($status_heading) ?></h2>
            <p style="margin: 10px 0 0 0;"><?= esc($status_message) ?></p>
        </div>

        <div class="subscription-details">
            <h3 style="color: #000f25; margin-top: 0;">Subscription Details</h3>

            <div class="detail-row">
                <span class="label">Previous Status:</span>
                <span class="status-badge status-<?= esc($old_status) ?>"><?= strtoupper(esc($old_status)) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Current Status:</span>
                <span class="status-badge status-<?= esc($new_status) ?>"><?= strtoupper(esc($new_status)) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Plan:</span>
                <span class="value"><?= esc($plan_name) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Date Changed:</span>
                <span class="value"><?= date('F j, Y g:i A', strtotime($date_changed)) ?></span>
            </div>

            <?php if (isset($expiry_date) && $expiry_date): ?>
            <div class="detail-row">
                <span class="label">Expires On:</span>
                <span class="value"><?= date('F j, Y', strtotime($expiry_date)) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($new_status === 'active'): ?>
        <div class="success-box">
            <h3 style="margin-top: 0; color: #047857;">✅ Your Subscription is Active</h3>
            <p><strong>Your business is now visible to drivers!</strong> This means:</p>
            <ul>
                <li>Your business appears on driver dashboards and maps</li>
                <li>All your locations and branches are visible</li>
                <li>Your services are displayed in search results</li>
                <li>You can receive and manage orders</li>
                <li>Full access to all subscription features</li>
            </ul>
        </div>
        <?php endif; ?>

        <?php if ($new_status === 'trial'): ?>
        <div class="info-box">
            <h3 style="margin-top: 0; color: #1e40af;">🎉 Your Trial Has Started</h3>
            <p><strong>Welcome to Truckers Africa!</strong> During your trial:</p>
            <ul>
                <li>Your business is visible to drivers</li>
                <li>Full access to all features</li>
                <li>No payment required until trial ends</li>
                <li>You'll receive a reminder before trial expires</li>
            </ul>
            <?php if (isset($trial_ends) && $trial_ends): ?>
            <p><strong>Trial Period Ends:</strong> <?= date('F j, Y', strtotime($trial_ends)) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($new_status === 'expired'): ?>
        <div class="warning-box">
            <h3 style="margin-top: 0; color: #856404;">⚠️ Immediate Action Required</h3>
            <p><strong>Your subscription has expired.</strong> This means:</p>
            <ul>
                <li>Your business is no longer visible to drivers</li>
                <li>All your locations and branches are hidden</li>
                <li>Your services are not displayed in search results</li>
                <li>You will not receive any new orders</li>
            </ul>
            <p><strong>To reactivate your subscription and restore visibility:</strong></p>
            <ol>
                <li>Log in to your merchant dashboard</li>
                <li>Go to Subscription Settings</li>
                <li>Update your payment method if needed</li>
                <li>Complete the payment to reactivate</li>
            </ol>
        </div>
        <?php endif; ?>

        <?php if ($new_status === 'cancelled'): ?>
        <div class="warning-box">
            <h3 style="margin-top: 0; color: #856404;">ℹ️ Subscription Cancelled</h3>
            <p><strong>Your subscription has been cancelled.</strong> This means:</p>
            <ul>
                <li>Your business is no longer visible to drivers</li>
                <li>All your locations and branches are hidden</li>
                <li>You will not receive any new orders</li>
            </ul>
            <p><strong>Want to come back?</strong> You can reactivate your subscription anytime by:</p>
            <ol>
                <li>Logging into your merchant dashboard</li>
                <li>Going to Subscription Settings</li>
                <li>Selecting a plan and completing payment</li>
            </ol>
        </div>
        <?php endif; ?>

        <?php if ($new_status === 'past_due'): ?>
        <div class="warning-box">
            <h3 style="margin-top: 0; color: #856404;">⚠️ Payment Overdue</h3>
            <p><strong>Your subscription payment is past due.</strong> To avoid service interruption:</p>
            <ul>
                <li>Update your payment method immediately</li>
                <li>Complete the outstanding payment</li>
                <li>Contact support if you need assistance</li>
            </ul>
            <p>If payment is not received soon, your subscription will be marked as expired and your business will be hidden from drivers.</p>
        </div>
        <?php endif; ?>

        <?php if (in_array($new_status, ['expired', 'cancelled', 'past_due'])): ?>
        <center>
            <a href="<?= base_url('merchant/subscription') ?>" class="button">Manage Subscription</a>
        </center>
        <?php else: ?>
        <center>
            <a href="<?= base_url('merchant/dashboard') ?>" class="button">Go to Dashboard</a>
        </center>
        <?php endif; ?>

        <?php if (isset($additional_message) && $additional_message): ?>
        <p><?= esc($additional_message) ?></p>
        <?php endif; ?>

        <p><strong>Need help?</strong> If you have questions or need assistance, please contact our support team.</p>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>For support: <a href="mailto:support@truckersafrica.com">support@truckersafrica.com</a></p>
    </div>
</body>
</html>
