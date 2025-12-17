<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Branch Manager Account Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .email-header {
            background-color: #0e2140;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header .logo-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        .email-header img {
            width: 50px;
            height: 50px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            display: inline-block;
        }
        .email-body {
            padding: 30px;
        }
        .email-body p {
            margin: 0 0 15px 0;
        }
        .branch-details {
            background-color: #f9fafb;
            border-left: 4px solid #0e2140;
            padding: 15px;
            margin: 20px 0;
        }
        .branch-details p {
            margin: 5px 0;
        }
        .setup-button {
            display: inline-block;
            background-color: #0e2140;
            color: #ffffff !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .setup-button:hover {
            background-color: #1a3a5f;
        }
        .warning-box {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 20px 0;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .button-container {
            text-align: center;
            margin: 25px 0;
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
    <div class="email-container">
        <div class="email-header">
            <div class="logo-title">
                <img src="https://truckersafrica.com/assets/images/logo-icon.png" alt="Truckers Africa Logo">
                <h1>Truckers Africa</h1>
            </div>
            <p>Branch Manager Account</p>
        </div>
        
        <div class="email-body">
            <h2 style="color: #1f2937; margin-top: 0;">Complete Your Branch Manager Account Setup</h2>
            
            <p>Hello <strong><?= esc($branchUser['full_name']) ?></strong>,</p>
            
            <p>A branch manager account has been created for you at <strong><?= esc($merchant['business_name']) ?></strong>! You will be managing the <strong><?= esc($location['location_name']) ?></strong> branch.</p>
            
            <div class="branch-details">
                <p><strong>Branch Details:</strong></p>
                <p>🏢 Business: <?= esc($merchant['business_name']) ?></p>
                <p>📍 Branch: <?= esc($location['location_name']) ?></p>
                <p>📧 Your Email: <?= esc($branchUser['email']) ?></p>
                <p>📱 Your Phone: <?= esc($branchUser['phone_number']) ?></p>
            </div>
            
            <p>To complete your account setup and gain access to your branch dashboard, you need to create your password.</p>
            
            <div class="button-container">
                <a href="<?= $setup_url ?>" class="setup-button">Set Up My Password</a>
            </div>
            
            <div class="warning-box">
                <p><strong>⏰ Important:</strong> This link is valid for <strong><?= $token_expires ?></strong>. Please complete your setup before it expires.</p>
            </div>
            
            <h3 style="color: #1f2937;">What's Next?</h3>
            <p>Once you set up your password, you'll be able to:</p>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Access your branch dashboard</li>
                <li>Manage orders for your branch location</li>
                <li>Request new listings for your branch</li>
                <li>Update branch information</li>
                <li>View branch performance and analytics</li>
            </ul>
            
            <h3 style="color: #1f2937;">Login Information</h3>
            <p>After setting up your password, you can log in at:</p>
            <p style="text-align: center; margin: 15px 0;">
                <a href="<?= site_url('branch/login') ?>" style="color: #0e2140; font-weight: bold;"><?= site_url('branch/login') ?></a>
            </p>
            
            <h3 style="color: #1f2937;">Need Help?</h3>
            <p>If you have any questions or need assistance, please contact your business owner or our support team.</p>
            
            <div class="alternative-link">
                <p><strong>Button not working?</strong> Copy and paste this link into your browser:</p>
                <p><?= $setup_url ?></p>
            </div>
        </div>
        
        <div class="email-footer">
            <p>This is an automated email from Truckers Africa.</p>
            <p>If you didn't expect this email, please contact the business owner or our support team immediately.</p>
            <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

