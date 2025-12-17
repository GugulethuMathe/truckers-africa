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
            background-color: #10b981;
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
            text-align: center;
        }
        .payment-details {
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
        .amount {
            font-size: 1.5em;
            font-weight: bold;
            color: #10b981;
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
        <h1>Payment Received</h1>
    </div>

    <div class="content">
        <p>Dear <?= esc($business_name) ?>,</p>

        <div class="success-badge">
            <h2 style="margin: 0; color: #065f46;">Payment Successful!</h2>
        </div>

        <p>Thank you for your payment. We have successfully received your payment and it has been processed.</p>

        <div class="payment-details">
            <h3 style="color: #000f25; margin-top: 0;">Payment Details</h3>

            <div class="detail-row">
                <span class="label">Amount:</span>
                <span class="value amount">R <?= number_format($amount, 2) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Payment Reference:</span>
                <span class="value"><?= esc($payment_reference) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Date:</span>
                <span class="value"><?= date('F j, Y g:i A', strtotime($payment_date)) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Description:</span>
                <span class="value"><?= esc($description) ?></span>
            </div>
        </div>

        <p>This serves as your payment receipt. Please keep this email for your records.</p>

        <center>
            <a href="<?= base_url('merchant/subscription') ?>" class="button">View Subscription Details</a>
        </center>

        <p>If you have any questions about this payment, please contact our support team.</p>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
    </div>
</body>
</html>
