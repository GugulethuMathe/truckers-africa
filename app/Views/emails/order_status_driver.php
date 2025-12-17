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
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .status-accepted {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-completed {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .order-details {
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
        <h1>Order Status Update</h1>
    </div>

    <div class="content">
        <p>Dear <?= esc($driver_name) ?>,</p>

        <p>Your order status has been updated:</p>

        <center>
            <div class="status-badge status-<?= strtolower($status) ?>">
                <?= esc($status) ?>
            </div>
        </center>

        <div class="order-details">
            <div class="detail-row">
                <span class="label">Booking Reference:</span>
                <span class="value"><?= esc($booking_reference) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Merchant:</span>
                <span class="value"><?= esc($merchant_name) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Location:</span>
                <span class="value"><?= esc($location_name) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Total Amount:</span>
                <span class="value"><?= esc($currency) ?> <?= number_format($total_amount, 2) ?></span>
            </div>
        </div>

        <?php if ($status === 'Accepted'): ?>
            <p>Great news! The merchant has accepted your order and is preparing it for you.</p>
        <?php elseif ($status === 'Rejected'): ?>
            <p>Unfortunately, the merchant was unable to fulfill your order at this time. Please contact them directly if you need more information.</p>
        <?php elseif ($status === 'Completed'): ?>
            <p>Your order has been completed. Thank you for using Truckers Africa!</p>
        <?php endif; ?>

        <center>
            <a href="<?= base_url('driver/orders') ?>" class="button">View Order Details</a>
        </center>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
    </div>
</body>
</html>
