<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listing Approved - Truckers Africa</title>
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
            background-color: #10b981;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header img {
            max-height: 60px;
            width: auto;
            margin-bottom: 15px;
            filter: brightness(0) invert(1);
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 30px;
        }
        .email-body p {
            margin: 0 0 15px 0;
        }
        .success-icon {
            text-align: center;
            font-size: 48px;
            color: #10b981;
            margin: 20px 0;
        }
        .listing-details {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .listing-details h3 {
            margin: 0 0 15px 0;
            color: #059669;
            font-size: 18px;
        }
        .listing-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .listing-details table td {
            padding: 8px 0;
            border-bottom: 1px solid #d1fae5;
        }
        .listing-details table td:first-child {
            font-weight: bold;
            color: #065f46;
            width: 40%;
        }
        .listing-details table tr:last-child td {
            border-bottom: none;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #10b981;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #059669;
        }
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        .email-footer a {
            color: #10b981;
            text-decoration: none;
        }
        .tips-section {
            background-color: #eff6ff;
            border-left: 4px solid: #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .tips-section h4 {
            margin: 0 0 10px 0;
            color: #1e40af;
        }
        .tips-section ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .tips-section li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="<?= base_url('assets/images/logo-white.png') ?>" alt="Truckers Africa">
            <h1>✅ Listing Approved!</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="success-icon">🎉</div>

            <p>Hello <strong><?= esc($branchUser['full_name']) ?></strong>,</p>

            <p>Great news! Your service listing has been approved by our admin team and is now <strong>live</strong> on Truckers Africa!</p>

            <!-- Listing Details -->
            <div class="listing-details">
                <h3>📋 Approved Listing Details</h3>
                <table>
                    <tr>
                        <td>Listing Title:</td>
                        <td><strong><?= esc($listing['title']) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Location:</td>
                        <td><?= esc($location['location_name']) ?></td>
                    </tr>
                    <tr>
                        <td>Price:</td>
                        <td><?= esc($listing['price']) ?> <?= esc($listing['currency_code']) ?></td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><span style="color: #10b981; font-weight: bold;">✓ APPROVED & LIVE</span></td>
                    </tr>
                    <tr>
                        <td>Approved On:</td>
                        <td><?= date('F j, Y \a\t g:i A') ?></td>
                    </tr>
                </table>
            </div>

            <p>Your listing is now visible to truck drivers across Africa who are searching for services in your area. You can expect to start receiving booking requests soon!</p>

            <!-- CTA -->
            <div class="cta-section">
                <a href="<?= site_url('branch/dashboard') ?>" class="button">View Your Dashboard</a>
            </div>

            <!-- Tips Section -->
            <div class="tips-section">
                <h4>💡 Tips to Get More Bookings:</h4>
                <ul>
                    <li><strong>Keep your information updated</strong> - Make sure your contact details and pricing are current</li>
                    <li><strong>Respond quickly</strong> - Fast response times lead to more bookings</li>
                    <li><strong>Provide excellent service</strong> - Happy customers will recommend you to other drivers</li>
                    <li><strong>Add more listings</strong> - Offer more services to attract more drivers</li>
                </ul>
            </div>

            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

            <p>Thank you for being part of the Truckers Africa community!</p>

            <p>Best regards,<br>
            <strong>The Truckers Africa Team</strong></p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>This email was sent to <?= esc($branchUser['email']) ?></p>
            <p>
                <a href="<?= site_url('/') ?>">Visit Truckers Africa</a> |
                <a href="<?= site_url('branch/help') ?>">Help & Support</a>
            </p>
            <p style="margin-top: 10px; font-size: 12px;">
                &copy; <?= date('Y') ?> Truckers Africa. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
