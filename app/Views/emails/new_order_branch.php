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
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .alert {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
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
            color: #000f25;
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
            color: #000f25;
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
            margin: 10px 5px;
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
        <h1>New Order for Your Location!</h1>
    </div>

    <div class="content">
        <div class="alert">
            <strong>Action Required:</strong> A new order has been placed for your location.
        </div>

        <p>Dear <?= esc($branch_name) ?>,</p>

        <p>You have received a new order at <strong><?= esc($location_name) ?></strong>. Please review and respond promptly.</p>

        <div class="order-details">
            <h3>Order Information</h3>

            <div class="detail-row">
                <span class="label">Booking Reference:</span>
                <span class="value"><?= esc($booking_reference) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Driver:</span>
                <span class="value"><?= esc($driver_name) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Order Date:</span>
                <span class="value"><?= date('F j, Y g:i A', strtotime($order_date)) ?></span>
            </div>

            <?php if (!empty($order_items)): ?>
            <div class="detail-row">
                <span class="label">Services/Products Ordered:</span>
                <div class="items-list">
                    <?php foreach ($order_items as $item): ?>
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

        <center>
            <a href="<?= base_url('branch/orders') ?>" class="button">View Order Details</a>
        </center>

        <p>Please log in to your branch dashboard to manage this order.</p>

        <p>Best regards,<br>
        <strong>Truckers Africa Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
    </div>
</body>
</html>
