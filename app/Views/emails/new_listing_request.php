<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Listing Request</title>
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
            background-color: #0e2140;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .detail-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #16a34a;
            border-radius: 4px;
        }
        .detail-label {
            font-weight: bold;
            color: #0e2140;
            margin-bottom: 5px;
        }
        .detail-value {
            color: #555;
        }
        .button {
            display: inline-block;
            background-color: #16a34a;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
        }
        .justification-box {
            background-color: #fef3c7;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔔 New Listing Request</h1>
    </div>

    <div class="content">
        <p>Hello <strong><?= esc($business_name) ?></strong>,</p>

        <p>You have received a new listing request from your branch manager <strong><?= esc($branch_name) ?></strong> at <strong><?= esc($location_name) ?></strong>.</p>

        <div class="detail-box">
            <div class="detail-label">Service Title:</div>
            <div class="detail-value"><?= esc($request_title) ?></div>
        </div>

        <?php if (!empty($request_description)): ?>
        <div class="detail-box">
            <div class="detail-label">Description:</div>
            <div class="detail-value"><?= esc($request_description) ?></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($suggested_price)): ?>
        <div class="detail-box">
            <div class="detail-label">Suggested Price:</div>
            <div class="detail-value"><?= esc($currency_code) ?> <?= number_format($suggested_price, 2) ?></div>
        </div>
        <?php endif; ?>

        <div class="justification-box">
            <div class="detail-label">Branch Manager's Justification:</div>
            <div class="detail-value"><?= nl2br(esc($justification)) ?></div>
        </div>

        <div class="detail-box">
            <div class="detail-label">Submitted On:</div>
            <div class="detail-value"><?= esc($request_date) ?></div>
        </div>

        <p style="text-align: center;">
            <a href="<?= esc($view_url) ?>" class="button">Review Request</a>
        </p>

        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <strong>What you can do:</strong><br>
            • Review the request details in your merchant dashboard<br>
            • Approve the request to create the listing<br>
            • Reject if it doesn't fit your business strategy<br>
            • Contact your branch manager for more information
        </p>
    </div>

    <div class="footer">
        <p>This is an automated notification from Truckers Africa</p>
        <p>© <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>Questions? Contact us at <a href="mailto:info@truckersafrica.com">info@truckersafrica.com</a></p>
    </div>
</body>
</html>
