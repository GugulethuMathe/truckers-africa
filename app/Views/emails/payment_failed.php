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
            background-color: #ef4444;
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
        .alert-badge {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 3px;
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
            font-size: 1.3em;
            font-weight: bold;
            color: #ef4444;
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
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠️ Payment Failed</h1>
    </div>

    <div class="content">
        <p>Dear <?= esc($business_name) ?>,</p>

        <div class="alert-badge">
            <h2 style="margin: 0; color: #991b1b;">Payment Unsuccessful</h2>
            <p style="margin: 10px 0 0 0;">We were unable to process your subscription payment.</p>
        </div>

        <p>We attempted to charge your payment method for your Truckers Africa subscription, but the payment was not successful.</p>

        <div class="payment-details">
            <h3 style="color: #000f25; margin-top: 0;">Payment Details</h3>

            <div class="detail-row">
                <span class="label">Amount:</span>
                <span class="value amount">R <?= number_format($amount, 2) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Date:</span>
                <span class="value"><?= date('F j, Y g:i A', strtotime($payment_date)) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value" style="color: #ef4444; font-weight: bold;">FAILED</span>
            </div>

            <?php if (isset($reason) && $reason): ?>
            <div class="detail-row">
                <span class="label">Reason:</span>
                <span class="value"><?= esc($reason) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="warning-box">
            <h3 style="margin-top: 0; color: #856404;">⚠️ Immediate Action Required</h3>
            <p><strong>Your subscription has been marked as expired.</strong> This means:</p>
            <ul>
                <li>Your business is no longer visible to drivers</li>
                <li>All your locations and branches are hidden</li>
                <li>Your services are not displayed in search results</li>
                <li>You will not receive any new orders</li>
            </ul>
        </div>

        <h3 style="color: #000f25;">Common Reasons for Payment Failure:</h3>
        <ul>
            <li>Insufficient funds in your account</li>
            <li>Expired or invalid payment card</li>
            <li>Payment card has been blocked or restricted</li>
            <li>Daily transaction limit exceeded</li>
            <li>Bank declined the transaction</li>
        </ul>

        <h3 style="color: #000f25;">How to Resolve This:</h3>
        <ol>
            <li>Log in to your merchant dashboard</li>
            <li>Go to Subscription Settings</li>
            <li>Update your payment method</li>
            <li>Complete the payment to reactivate your subscription</li>
        </ol>

        <center>
            <a href="<?= base_url('merchant/subscription') ?>" class="button">Update Payment Method</a>
        </center>

        <p><strong>Need help?</strong> If you believe this is an error or need assistance, please contact our support team immediately.</p>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>For support: <a href="mailto:support@truckersafrica.com">support@truckersafrica.com</a></p>
    </div>
</body>
</html>
