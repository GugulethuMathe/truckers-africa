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
            background-color: #2f855a;
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .welcome-box {
            background-color: #d1fae5;
            border-left: 4px solid #2f855a;
            padding: 20px;
            margin: 20px 0;
            border-radius: 3px;
        }
        .features {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .feature-item {
            padding: 10px 0;
            display: flex;
            align-items: start;
        }
        .feature-icon {
            color: #2f855a;
            margin-right: 10px;
            font-weight: bold;
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
            background-color: #2f855a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to Truckers Africa!</h1>
    </div>

    <div class="content">
        <div class="welcome-box">
            <h2 style="margin-top: 0; color: #065f46;">Hello <?= esc($driver_name) ?>!</h2>
            <p style="margin-bottom: 0;">Thank you for joining Truckers Africa. We're excited to have you on board!</p>
        </div>

        <p>You're now part of Africa's leading platform connecting truck drivers with essential services across the continent.</p>

        <div class="features">
            <h3 style="color: #2f855a; margin-top: 0;">What You Can Do:</h3>

            <div class="feature-item">
                <span class="feature-icon">✓</span>
                <div>
                    <strong>Find Nearby Services:</strong> Locate mechanics, rest stops, border clearing agents, and more along your route.
                </div>
            </div>

            <div class="feature-item">
                <span class="feature-icon">✓</span>
                <div>
                    <strong>Plan Routes:</strong> Plan your trips and discover services along the way.
                </div>
            </div>

            <div class="feature-item">
                <span class="feature-icon">✓</span>
                <div>
                    <strong>Order Services:</strong> Book services directly through the platform and track your orders.
                </div>
            </div>

            <div class="feature-item">
                <span class="feature-icon">✓</span>
                <div>
                    <strong>Save Favorites:</strong> Save your favorite merchants for quick access later.
                </div>
            </div>

            <div class="feature-item">
                <span class="feature-icon">✓</span>
                <div>
                    <strong>Get Updates:</strong> Receive real-time notifications about your orders and new services.
                </div>
            </div>
        </div>

        <center>
            <a href="<?= base_url('login') ?>" class="button">Login to Your Account</a>
        </center>

        <p><strong>Need Help?</strong><br>
        If you have any questions or need assistance, don't hesitate to reach out to our support team.</p>

        <p>Safe travels!</p>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>You're receiving this email because you registered on Truckers Africa.</p>
    </div>
</body>
</html>
