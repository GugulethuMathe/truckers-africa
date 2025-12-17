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
            background-color: #dbeafe;
            border-left: 4px solid #000f25;
            padding: 20px;
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
        .step {
            padding: 10px 0;
            display: flex;
            align-items: start;
        }
        .step-number {
            background-color: #000f25;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            flex-shrink: 0;
            font-weight: bold;
            font-size: 0.9em;
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
        <h1>Welcome to Truckers Africa!</h1>
        <p style="margin: 0;">Merchant Registration Received</p>
    </div>

    <div class="content">
        <div class="welcome-box">
            <h2 style="margin-top: 0; color: #1e40af;">Hello <?= esc($contact_name) ?>!</h2>
            <p style="margin-bottom: 0;">Thank you for registering <strong><?= esc($business_name) ?></strong> on Truckers Africa.</p>
        </div>

        <p>We've received your merchant application and our team is currently reviewing it. This process typically takes 1-3 business days.</p>

        <div class="next-steps">
            <h3 style="color: #000f25; margin-top: 0;">What Happens Next:</h3>

            <div class="step">
                <div class="step-number">1</div>
                <div>
                    <strong>Application Review:</strong> Our team will verify your business information and documentation.
                </div>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <div>
                    <strong>Approval Notification:</strong> You'll receive an email once your application has been reviewed.
                </div>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <div>
                    <strong>Choose a Plan:</strong> Upon approval, select a subscription plan that suits your business needs.
                </div>
            </div>

            <div class="step">
                <div class="step-number">4</div>
                <div>
                    <strong>Start Listing:</strong> Add your services and products to start connecting with truck drivers!
                </div>
            </div>
        </div>

        <p><strong>Why Join Truckers Africa?</strong></p>
        <ul>
            <li>Reach thousands of truck drivers across Africa</li>
            <li>Manage multiple business locations easily</li>
            <li>Receive and track orders in real-time</li>
            <li>Build your reputation with reviews and ratings</li>
            <li>Grow your business with our marketing support</li>
        </ul>

        <p>In the meantime, feel free to explore our platform and familiarize yourself with the features.</p>

        <center>
            <a href="<?= base_url('login') ?>" class="button">Login to Dashboard</a>
        </center>

        <p>If you have any questions, our support team is here to help!</p>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>You're receiving this email because you registered as a merchant on Truckers Africa.</p>
    </div>
</body>
</html>
