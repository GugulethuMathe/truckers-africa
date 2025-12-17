<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Account Setup</title>
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
            padding: 30px 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header img {
            width: 50px;
            height: 50px;
            vertical-align: middle;
            margin-right: 10px;
        }
        .header h1 {
            display: inline-block;
            vertical-align: middle;
            margin: 0;
        }
        .header .logo-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .welcome-box {
            background-color: #dbeafe;
            border-left: 4px solid #000f25;
            padding: 20px;
            margin: 20px 0;
            border-radius: 3px;
        }
        .merchant-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .merchant-details p {
            margin: 5px 0;
        }
        .warning-box {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 20px 0;
            border-radius: 3px;
        }
        .next-steps {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
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
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .alternative-link {
            margin-top: 20px;
            padding: 15px;
            background-color: #f3f4f6;
            border-radius: 4px;
            word-break: break-all;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-title">
            <img src="https://truckersafrica.com/assets/images/logo-icon.png" alt="Truckers Africa Logo">
            <h1>Truckers Africa</h1>
        </div>
        <p style="margin: 0;">Complete Your Account Setup</p>
    </div>

    <div class="content">
        <div class="welcome-box">
            <h2 style="margin-top: 0; color: #1e40af;">Welcome to Truckers Africa!</h2>
            <p style="margin-bottom: 0;">Your merchant account has been created. Let's get you set up!</p>
        </div>

        <p>Hello <strong><?= esc($merchant['owner_name']) ?></strong>,</p>

        <p>Your merchant account has been created by our admin team! We're excited to have <strong><?= esc($merchant['business_name']) ?></strong> join the Truckers Africa platform.</p>

        <div class="merchant-details">
            <h3 style="color: #000f25; margin-top: 0;">Account Details:</h3>
            <p>📧 Email: <?= esc($merchant['email']) ?></p>
            <p>🏢 Business: <?= esc($merchant['business_name']) ?></p>
            <p>📱 Contact: <?= esc($merchant['business_contact_number']) ?></p>
        </div>

        <p>To complete your account setup and gain access to your merchant dashboard, you need to create your password.</p>

        <center>
            <a href="<?= $setup_url ?>" class="button">Set Up My Password</a>
        </center>

        <div class="warning-box">
            <p style="margin: 0;"><strong>⏰ Important:</strong> This link is valid for <strong><?= $token_expires ?></strong>. Please complete your setup before it expires.</p>
        </div>

        <div class="next-steps">
            <h3 style="color: #000f25; margin-top: 0;">What's Next?</h3>
            <p>Once you set up your password, you'll be able to:</p>
            <ul>
                <li>Access your merchant dashboard</li>
                <li>Create and manage your listings</li>
                <li>Receive and process orders from truck drivers</li>
                <li>Update your business profile and information</li>
                <li>Track your sales and performance</li>
            </ul>
        </div>

        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

        <div class="alternative-link">
            <p><strong>Button not working?</strong> Copy and paste this link into your browser:</p>
            <p><?= $setup_url ?></p>
        </div>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated email from Truckers Africa.</p>
        <p>If you didn't expect this email, please contact our support team immediately.</p>
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
    </div>
</body>
</html>

