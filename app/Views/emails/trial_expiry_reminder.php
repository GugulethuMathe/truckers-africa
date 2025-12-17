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
            background-color: #f59e0b;
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
            background-color: #fff3cd;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 3px;
        }
        .trial-details {
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
        .countdown {
            font-size: 2em;
            font-weight: bold;
            color: #f59e0b;
            text-align: center;
            margin: 20px 0;
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
            font-weight: bold;
        }
        .info-box {
            background-color: #e8f4f8;
            border: 1px solid #3b82f6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .plan-comparison {
            background-color: white;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .features-list {
            list-style: none;
            padding: 0;
        }
        .features-list li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }
        .features-list li:before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⏰ Your Trial is Ending Soon</h1>
    </div>

    <div class="content">
        <p>Dear <?= esc($business_name) ?>,</p>

        <div class="alert-badge">
            <h2 style="margin: 0; color: #92400e;">Your Free Trial Ends Soon</h2>
            <p style="margin: 10px 0 0 0;">Don't lose access to thousands of potential customers!</p>
        </div>

        <div class="countdown">
            <?= esc($days_remaining) ?> <?= $days_remaining == 1 ? 'Day' : 'Days' ?> Remaining
        </div>

        <p>Your free trial period is coming to an end. We hope you've been enjoying the benefits of being visible to truck drivers across Africa!</p>

        <div class="trial-details">
            <h3 style="color: #000f25; margin-top: 0;">Trial Details</h3>

            <div class="detail-row">
                <span class="label">Trial Started:</span>
                <span class="value"><?= date('F j, Y', strtotime($trial_started)) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Trial Ends:</span>
                <span class="value"><?= date('F j, Y', strtotime($trial_ends)) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Current Plan:</span>
                <span class="value"><?= esc($plan_name) ?></span>
            </div>
        </div>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #1e40af;">📊 Your Trial Performance</h3>
            <?php if (isset($performance_stats) && $performance_stats): ?>
            <p>During your trial period:</p>
            <ul>
                <?php if (isset($performance_stats['profile_views'])): ?>
                <li><strong><?= number_format($performance_stats['profile_views']) ?></strong> drivers viewed your business profile</li>
                <?php endif; ?>
                <?php if (isset($performance_stats['orders_received'])): ?>
                <li><strong><?= number_format($performance_stats['orders_received']) ?></strong> orders received</li>
                <?php endif; ?>
                <?php if (isset($performance_stats['route_appearances'])): ?>
                <li><strong><?= number_format($performance_stats['route_appearances']) ?></strong> appearances in driver routes</li>
                <?php endif; ?>
            </ul>
            <?php else: ?>
            <p>Your business has been visible to thousands of truck drivers across the network. Continue this visibility by subscribing to a plan!</p>
            <?php endif; ?>
        </div>

        <h3 style="color: #000f25;">What Happens Next?</h3>

        <p><strong>If you subscribe before your trial ends:</strong></p>
        <ul class="features-list">
            <li>Uninterrupted service - your business stays visible</li>
            <li>Continue receiving orders from drivers</li>
            <li>Access to all premium features</li>
            <li>Priority support</li>
        </ul>

        <p><strong>If your trial expires without subscription:</strong></p>
        <ul style="list-style: none; padding-left: 25px;">
            <li style="padding: 8px 0; position: relative; padding-left: 0;">❌ Your business will be hidden from all drivers</li>
            <li style="padding: 8px 0; position: relative; padding-left: 0;">❌ You won't receive any new orders</li>
            <li style="padding: 8px 0; position: relative; padding-left: 0;">❌ Your locations and services won't appear in searches</li>
            <li style="padding: 8px 0; position: relative; padding-left: 0;">❌ You'll lose access to the dashboard</li>
        </ul>

        <div class="plan-comparison">
            <h3 style="color: #000f25; margin-top: 0;">Continue with <?= esc($plan_name) ?></h3>
            <p style="font-size: 1.5em; color: #000f25; margin: 10px 0;">
                <strong>R <?= number_format($plan_price, 2) ?></strong> / month
            </p>
            <?php if (isset($plan_features) && $plan_features): ?>
            <ul class="features-list">
                <?php foreach ($plan_features as $feature): ?>
                <li><?= esc($feature) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <center>
            <a href="<?= base_url('merchant/subscription/plans') ?>" class="button">Subscribe Now & Stay Visible</a>
        </center>

        <p style="text-align: center; color: #666; font-size: 0.9em; margin-top: 20px;">
            Subscribe now to avoid any interruption to your service
        </p>

        <h3 style="color: #000f25;">Need Help Choosing a Plan?</h3>
        <p>Our team is here to help you select the best plan for your business needs. Contact us at <a href="mailto:support@truckersafrica.com">support@truckersafrica.com</a> or call us for assistance.</p>

        <?php if (isset($additional_message) && $additional_message): ?>
        <div class="info-box">
            <p style="margin: 0;"><?= esc($additional_message) ?></p>
        </div>
        <?php endif; ?>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>This is an automated reminder email.</p>
        <p>For support: <a href="mailto:support@truckersafrica.com">support@truckersafrica.com</a></p>
    </div>
</body>
</html>
