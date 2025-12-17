<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Primary Business Location Has Been Created</title>
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
        .location-details {
            background-color: #f9fafb;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 20px 0;
        }
        .location-details p {
            margin: 5px 0;
        }
        .info-box {
            background-color: #dbeafe;
            border-left: 4px solid #3b82f6;
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
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="logo-title">
                <img src="https://truckersafrica.com/assets/images/logo-icon.png" alt="Truckers Africa Logo">
                <h1>Truckers Africa</h1>
            </div>
            <p>Business Location Created</p>
        </div>
        
        <div class="email-body">
            <h2 style="color: #1f2937; margin-top: 0;">✅ Your Primary Business Location Has Been Created</h2>
            
            <p>Hello <strong><?= esc($merchant['owner_name']) ?></strong>,</p>
            
            <p>Great news! We've automatically created your primary business location based on the address you provided in your profile. This allows you to start creating service listings right away!</p>
            
            <div class="location-details">
                <p><strong>📍 Location Details:</strong></p>
                <p>🏢 Business: <?= esc($merchant['business_name']) ?></p>
                <p>📌 Branch Name: <?= esc($location['location_name']) ?></p>
                <p>📍 Address: <?= esc($location['physical_address']) ?></p>
                <p>📱 Contact: <?= esc($location['contact_number']) ?></p>
            </div>
            
            <div class="info-box">
                <p><strong>ℹ️ What This Means:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>You can now create service listings for this location</li>
                    <li>Drivers can discover your business on the map</li>
                    <li>You can receive and manage orders at this location</li>
                    <li>A branch manager account has been created for this location</li>
                </ul>
            </div>
            
            <h3 style="color: #1f2937;">Branch Manager Account</h3>
            <p>We've also created a branch manager account for this location:</p>
            <p style="margin-left: 20px;">📧 Email: <strong><?= esc($branch_email) ?></strong></p>
            <p>This account can be used to manage orders and operations specifically for this branch. You can set up the password using the link sent in a separate email, or you can manage it from your merchant dashboard.</p>
            
            <h3 style="color: #1f2937;">What's Next?</h3>
            <p>Now that your primary location is set up, you can:</p>
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li><strong>Create Service Listings:</strong> Add the services and products you offer at this location</li>
                <li><strong>Add More Locations:</strong> If you have multiple branches, you can add them from your dashboard</li>
                <li><strong>Manage Your Branch:</strong> Update location details, operating hours, and contact information</li>
                <li><strong>Start Receiving Orders:</strong> Once your listings are approved, drivers can place orders</li>
            </ol>
            
            <p style="text-align: center; margin: 25px 0;">
                <a href="<?= site_url('merchant/dashboard') ?>" style="display: inline-block; background-color: #0e2140; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: bold;">Go to My Dashboard</a>
            </p>
            
            <h3 style="color: #1f2937;">Need Help?</h3>
            <p>If you have any questions or need to update your location details, visit your <a href="<?= site_url('merchant/locations') ?>" style="color: #0e2140;">Business Locations</a> page.</p>
        </div>
        
        <div class="email-footer">
            <p>This is an automated email from Truckers Africa.</p>
            <p>Your primary location was created automatically when you updated your business profile.</p>
            <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

