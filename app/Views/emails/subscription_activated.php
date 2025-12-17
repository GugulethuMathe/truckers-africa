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
            background-color: #000f25;
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
        .success-badge {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Subscription Activated!</h1>
    </div>

    <div class="content">
        <p>Dear <?= esc($business_name) ?>,</p>

        <div class="success-badge">
            <strong>Success!</strong> Your subscription has been activated and is now live.
        </div>

        <p>Thank you for subscribing to Truckers Africa. Your merchant listings are now visible to truck drivers across the platform!</p>

        <div class="subscription-details">
            <h3 style="color: #000f25; margin-top: 0;">Subscription Details</h3>

            <div class="detail-row">
                <span class="label">Plan:</span>
                <span class="value"><?= esc($plan_name) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Amount Paid:</span>
                <span class="value">R <?= number_format($amount, 2) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Start Date:</span>
                <span class="value"><?= date('F j, Y', strtotime($start_date)) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Expiry Date:</span>
                <span class="value"><?= date('F j, Y', strtotime($end_date)) ?></span>
            </div>
        </div>

        <p>You can now enjoy all the benefits of your subscription plan. Don't forget to keep your listings and profile up to date!</p>

        <center>
            <a href="<?= base_url('merchant/dashboard') ?>" class="button">Go to Dashboard</a>
        </center>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
    </div>
</body>
</html>
