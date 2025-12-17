<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Accepted!</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #16a34a; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .order-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .merchant-details { background: #e0f2fe; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #0284c7; }
        .button { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .celebration { font-size: 48px; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Great News! Order Accepted!</h1>
            <p>Truckers Africa</p>
        </div>
        
        <div class="content">
            <div class="celebration">🎉</div>
            
            <h2>Hello <?= esc($driver['name']) ?>,</h2>
            
            <p><strong>Excellent news!</strong> Your order has been accepted and is now being prepared for you.</p>
            
            <div class="order-details">
                <h3>Order Information</h3>
                <p><strong>Order ID:</strong> #<?= esc($order['booking_reference']) ?></p>
                <p><strong>Total Amount:</strong> R<?= number_format($order['grand_total'], 2) ?></p>
                <p><strong>Status:</strong> <span style="color: #16a34a; font-weight: bold;">✓ ACCEPTED</span></p>
                <p><strong>Order Date:</strong> <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
            </div>
            
            <div class="merchant-details">
                <h3>Merchant Details</h3>
                <p><strong>Business:</strong> <?= esc($merchant['business_name']) ?></p>
                <?php if (!empty($merchant['business_contact_number'])): ?>
                <p><strong>Contact:</strong> <?= esc($merchant['business_contact_number']) ?></p>
                <?php elseif (!empty($merchant['contact_number'])): ?>
                <p><strong>Contact:</strong> <?= esc($merchant['contact_number']) ?></p>
                <?php elseif (!empty($merchant['phone_number'])): ?>
                <p><strong>Contact:</strong> <?= esc($merchant['phone_number']) ?></p>
                <?php elseif (!empty($merchant['phone'])): ?>
                <p><strong>Contact:</strong> <?= esc($merchant['phone']) ?></p>
                <?php endif; ?>
                <?php if (!empty($merchant['whatsapp_number'])): ?>
                <p><strong>WhatsApp:</strong> <?= esc($merchant['whatsapp_number']) ?></p>
                <?php endif; ?>
                <?php if (!empty($merchant['location'])): ?>
                <p><strong>Location:</strong> <?= esc($merchant['location']) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($merchant['business_description'])): ?>
                <p><strong>About:</strong> <?= esc($merchant['business_description']) ?></p>
                <?php endif; ?>
            </div>
            
            <h3>Next Steps:</h3>
            <ol>
                <li><strong>Contact Merchant:</strong> You can reach out to confirm pickup/delivery details.</li>
                <li><strong>Prepare for Service:</strong> Make sure you're ready for the estimated arrival time.</li>
                <li><strong>Complete Order:</strong> Enjoy your services and complete the order when done.</li>
            </ol>
            
            <?php if (!empty($order['estimated_arrival'])): ?>
            <div style="background: #fef3c7; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #f59e0b;">
                <p><strong>⏰ Estimated Arrival:</strong> <?= date('F j, Y \a\t g:i A', strtotime($order['estimated_arrival'])) ?></p>
                <p style="margin: 0; font-size: 14px; color: #92400e;">Please be available around this time for the best service experience.</p>
            </div>
            <?php endif; ?>
            
            <p style="text-align: center;">
                <a href="<?= site_url('driver/orders/view/' . $order['id']) ?>" class="button">View Order Details</a>
                <a href="<?= site_url('merchant/profile/' . $merchant['id']) ?>" class="button" style="background: #16a34a;">View Merchant Profile</a>
            </p>
            
            <p><strong>Questions?</strong> Feel free to contact the merchant directly or reach out to our support team if you need any assistance.</p>
        </div>
        
        <div class="footer">
            <p>This email was sent by Truckers Africa</p>
            <p>Have a great experience with your order!</p>
            <p><a href="<?= site_url() ?>">Visit Truckers Africa</a></p>
        </div>
    </div>
</body>
</html>
