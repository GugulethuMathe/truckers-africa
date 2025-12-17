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
        .warning-badge {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 3px;
        }
        .days-remaining {
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
            background-color: #f59e0b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Subscription Expiring Soon</h1>
    </div>

    <div class="content">
        <p>Dear <?= esc($business_name) ?>,</p>

        <div class="warning-badge">
            <strong>Important Reminder:</strong> Your subscription is expiring soon!
        </div>

        <div class="days-remaining">
            <?= esc($days_remaining) ?> Day<?= $days_remaining != 1 ? 's' : '' ?> Remaining
        </div>

        <p>Your <strong><?= esc($plan_name) ?></strong> subscription will expire on <strong><?= date('F j, Y', strtotime($end_date)) ?></strong>.</p>

        <p>To continue enjoying uninterrupted access to Truckers Africa and keep your listings visible to drivers, please renew your subscription before it expires.</p>

        <center>
            <a href="<?= base_url('merchant/subscription/renew') ?>" class="button">Renew Subscription Now</a>
        </center>

        <p><strong>What happens if your subscription expires?</strong></p>
        <ul>
            <li>Your listings will no longer be visible to drivers</li>
            <li>You won't receive new orders</li>
            <li>Access to premium features will be restricted</li>
        </ul>

        <p>Don't miss out on potential customers. Renew today!</p>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
    </div>
</body>
</html>
