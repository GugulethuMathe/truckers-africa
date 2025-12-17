<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #16a34a; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .order-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .button { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .success-icon { font-size: 48px; color: #16a34a; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Placed Successfully!</h1>
            <p>Truckers Africa</p>
        </div>
        
        <div class="content">
            <div class="success-icon">✓</div>
            
            <h2>Hello <?= esc($driver['name']) ?>,</h2>
            
            <p>Thank you for placing your order with Truckers Africa! Your order has been successfully submitted and merchants are being notified.</p>
            
            <div class="order-details">
                <h3>Your Order Summary</h3>
                <p><strong>Order ID:</strong> #<?= esc($order['booking_reference']) ?></p>
                <p><strong>Total Amount:</strong> R<?= number_format($order['grand_total'], 2) ?></p>
                <p><strong>Order Date:</strong> <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
                <p><strong>Status:</strong> <span style="color: #f59e0b;">Pending Merchant Response</span></p>
                
                <?php if (!empty($order['vehicle_model'])): ?>
                <p><strong>Vehicle:</strong> <?= esc($order['vehicle_model']) ?> (<?= esc($order['vehicle_license_plate']) ?>)</p>
                <?php endif; ?>
                
                <?php if (!empty($order['estimated_arrival'])): ?>
                <p><strong>Estimated Arrival:</strong> <?= date('F j, Y \a\t g:i A', strtotime($order['estimated_arrival'])) ?></p>
                <?php endif; ?>
            </div>
            
            <h3>What happens next?</h3>
            <ol>
                <li><strong>Merchant Review:</strong> The merchants will review your order and respond within a few hours.</li>
                <li><strong>Confirmation:</strong> You'll receive notifications when merchants accept or decline your order.</li>
                <li><strong>Preparation:</strong> Once accepted, merchants will prepare your items for pickup/delivery.</li>
                <li><strong>Completion:</strong> Complete your journey and enjoy your services!</li>
            </ol>
            
            <p style="text-align: center;">
                <a href="<?= site_url('driver/orders/view/' . $order['id']) ?>" class="button">Track Your Order</a>
            </p>
            
            <p><strong>Need Help?</strong> If you have any questions about your order, you can contact us or view your order status in your driver dashboard.</p>
        </div>
        
        <div class="footer">
            <p>This email was sent by Truckers Africa</p>
            <p>You will receive updates as your order status changes.</p>
            <p><a href="<?= site_url() ?>">Visit Truckers Africa</a></p>
        </div>
    </div>
</body>
</html>
