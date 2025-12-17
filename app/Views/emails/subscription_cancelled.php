<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Cancelled</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .info-box {
            background: white;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 28px;">Subscription Cancelled</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">We're sorry to see you go</p>
    </div>

    <div class="content">
        <p>Hello <strong><?= esc($merchant_name) ?></strong>,</p>

        <p>Your subscription to the <strong><?= esc($plan_name) ?></strong> plan has been successfully cancelled.</p>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #1e40af;">📅 Access Until: <?= esc($access_until) ?></h3>
            <p style="margin-bottom: 0;">
                You will continue to have full access to your account and all features until <strong><?= esc($access_until) ?></strong>. 
                After this date, your business will no longer be visible to drivers.
            </p>
        </div>

        <div class="warning-box">
            <h3 style="margin-top: 0; color: #92400e;">⚠️ What Happens Next</h3>
            <p>After your subscription ends on <strong><?= esc($access_until) ?></strong>:</p>
            <ul style="margin-bottom: 0;">
                <li>Your business will be hidden from driver searches</li>
                <li>All your locations and branches will be deactivated</li>
                <li>You will not receive any new orders</li>
                <li>Your data will be preserved for 90 days</li>
            </ul>
        </div>

        <h3 style="color: #1f2937; margin-top: 30px;">Cancellation Reason</h3>
        <p style="background: white; padding: 12px; border-radius: 6px; border: 1px solid #e5e7eb;">
            <strong><?= esc($cancellation_reason) ?></strong>
        </p>

        <p style="margin-top: 30px;">
            We value your feedback and would love to understand how we can improve. If you have any additional 
            comments or concerns, please don't hesitate to reach out to our support team.
        </p>

        <h3 style="color: #1f2937; margin-top: 30px;">💚 Come Back Anytime!</h3>
        <p>
            Changed your mind? You can reactivate your subscription at any time before <?= esc($access_until) ?> 
            to keep your business visible without interruption.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="<?= site_url('merchant/subscription/plans') ?>" class="button">
                Reactivate Subscription
            </a>
        </div>

        <div style="background: white; padding: 20px; border-radius: 6px; margin-top: 30px; border: 1px solid #e5e7eb;">
            <h4 style="margin-top: 0; color: #1f2937;">Need Help?</h4>
            <p style="margin-bottom: 10px;">Our support team is here to assist you:</p>
            <p style="margin: 5px 0;">
                📧 Email: <a href="mailto:support@truckersafrica.com" style="color: #3b82f6;">support@truckersafrica.com</a>
            </p>
            <p style="margin: 5px 0;">
                📱 Phone/WhatsApp: <a href="tel:+27687781223" style="color: #3b82f6;">+27 68 778 1223</a>
            </p>
            <p style="margin: 5px 0;">
                🕐 Hours: Monday - Friday, 8:00 AM - 5:00 PM (SAST)
            </p>
        </div>

        <p style="margin-top: 30px;">
            Thank you for being part of the Truckers Africa community. We hope to serve you again in the future!
        </p>

        <p style="margin-top: 20px;">
            Best regards,<br>
            <strong>The Truckers Africa Team</strong>
        </p>
    </div>

    <div class="footer">
        <p style="margin: 5px 0;">© <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p style="margin: 5px 0;">
            <a href="<?= site_url('/') ?>" style="color: #3b82f6; text-decoration: none;">Visit Website</a> | 
            <a href="<?= site_url('merchant/help') ?>" style="color: #3b82f6; text-decoration: none;">Help Center</a>
        </p>
    </div>
</body>
</html>

