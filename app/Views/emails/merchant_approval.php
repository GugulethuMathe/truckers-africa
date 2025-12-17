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
        .status-badge {
            display: inline-block;
            padding: 15px 30px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 1.2em;
            margin: 20px 0;
        }
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
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
            background-color: #e0f2fe;
            border-left: 4px solid #0284c7;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Merchant Application Status</h1>
    </div>

    <div class="content">
        <p>Dear <?= esc($contact_name) ?>,</p>

        <center>
            <div class="status-badge status-<?= strtolower($status) ?>">
                Application <?= esc($status) ?>
            </div>
        </center>

        <?php if ($status === 'Approved'): ?>
            <p>Congratulations! Your merchant application for <strong><?= esc($business_name) ?></strong> has been approved.</p>

            <div class="info-box">
                <strong>Next Steps:</strong>
                <ol>
                    <li>Log in to your merchant dashboard</li>
                    <li>Complete your business profile</li>
                    <li>Choose a subscription plan</li>
                    <li>Start adding your services and products</li>
                </ol>
            </div>

            <p>You can now start connecting with truck drivers across Africa and grow your business with Truckers Africa!</p>

            <center>
                <a href="<?= base_url('merchant/dashboard') ?>" class="button">Go to Dashboard</a>
            </center>

        <?php else: ?>
            <p>Thank you for your interest in joining Truckers Africa as a merchant partner.</p>

            <p>After careful review, we regret to inform you that we are unable to approve your merchant application for <strong><?= esc($business_name) ?></strong> at this time.</p>

            <p>If you have any questions or would like to discuss this decision, please contact our support team.</p>

            <center>
                <a href="mailto:admin@truckersafrica.com" class="button">Contact Support</a>
            </center>
        <?php endif; ?>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
    </div>
</body>
</html>
