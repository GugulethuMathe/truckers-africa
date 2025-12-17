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
        .order-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .order-details h3 {
            margin-top: 0;
            color: #2f855a;
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
        .total {
            font-size: 1.2em;
            font-weight: bold;
            color: #2f855a;
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
        .items-list {
            margin: 15px 0;
        }
        .item {
            padding: 10px;
            background-color: #f8f9fa;
            margin: 5px 0;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Order Confirmation</h1>
    </div>

    <div class="content">
        <p>Dear <?= esc($driver_name) ?>,</p>

        <p>Thank you for your order! We're pleased to confirm that your order has been successfully placed.</p>

        <div class="order-details">
            <h3>Order Details</h3>

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
                <span class="label">Order Date:</span>
                <span class="value"><?= date('F j, Y g:i A', strtotime($order_date)) ?></span>
            </div>

            <?php if (!empty($items)): ?>
            <div class="detail-row">
                <span class="label">Services/Products Ordered:</span>
                <div class="items-list">
                    <?php foreach ($items as $item): ?>
                        <div class="item">
                            <strong><?= esc($item['name'] ?? $item['title'] ?? 'Service') ?></strong>
                            <br>
                            <small style="color: #666;">Quantity: <?= esc($item['quantity']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-row">
                <span class="label">Total Amount:</span>
                <span class="value total"><?= esc($currency) ?> <?= number_format($total_amount, 2) ?></span>
            </div>
        </div>

        <p>The merchant will review your order and update you on its status. You can track your order status in the app.</p>

        <center>
            <a href="<?= base_url('driver/orders') ?>" class="button">View My Orders</a>
        </center>

        <p>If you have any questions, please don't hesitate to contact the merchant directly.</p>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
    </div>
</body>
</html>
