<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Accepted - Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #16a34a; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .order-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .driver-details { background: #e0f2fe; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #0284c7; }
        .button { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Accepted Successfully</h1>
            <p>Truckers Africa</p>
        </div>
        
        <div class="content">
            <h2>Hello <?= esc($merchant['business_name']) ?>,</h2>
            
            <p>You have successfully accepted an order. The driver has been notified and is expecting your service.</p>
            
            <div class="order-details">
                <h3>Order Summary</h3>
                <p><strong>Order ID:</strong> #<?= esc($order['booking_reference']) ?></p>
                <p><strong>Total Amount:</strong> R<?= number_format($order['grand_total'], 2) ?></p>
                <p><strong>Status:</strong> <span style="color: #16a34a; font-weight: bold;">✓ ACCEPTED</span></p>
                <p><strong>Order Date:</strong> <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
                <p><strong>Accepted On:</strong> <?= date('F j, Y \a\t g:i A') ?></p>
            </div>
            
            <div class="driver-details">
                <h3>Driver Information</h3>
                <p><strong>Name:</strong> <?= esc($driver['name'] . ' ' . $driver['surname']) ?></p>
                <?php if (!empty($driver['contact_number'])): ?>
                <p><strong>Contact:</strong> <?= esc($driver['contact_number']) ?></p>
                <?php elseif (!empty($driver['phone_number'])): ?>
                <p><strong>Contact:</strong> <?= esc($driver['phone_number']) ?></p>
                <?php elseif (!empty($driver['phone'])): ?>
                <p><strong>Contact:</strong> <?= esc($driver['phone']) ?></p>
                <?php endif; ?>
                <?php if (!empty($driver['whatsapp_number'])): ?>
                <p><strong>WhatsApp:</strong> <?= esc($driver['whatsapp_number']) ?></p>
                <?php endif; ?>
                <p><strong>Email:</strong> <?= esc($driver['email']) ?></p>
                
                <?php if (!empty($order['vehicle_model'])): ?>
                <p><strong>Vehicle:</strong> <?= esc($order['vehicle_model']) ?> (<?= esc($order['vehicle_license_plate']) ?>)</p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($order['estimated_arrival'])): ?>
            <div style="background: #fef3c7; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #f59e0b;">
                <p><strong>⏰ Estimated Arrival:</strong> <?= date('F j, Y \a\t g:i A', strtotime($order['estimated_arrival'])) ?></p>
                <p style="margin: 0; font-size: 14px; color: #92400e;">Please be prepared to serve the driver around this time.</p>
            </div>
            <?php endif; ?>
            
            <h3>Your Responsibilities:</h3>
            <ul>
                <li><strong>Prepare Services:</strong> Get your services/products ready for the driver</li>
                <li><strong>Be Available:</strong> Ensure you're available during the estimated arrival time</li>
                <li><strong>Quality Service:</strong> Provide excellent service to maintain your reputation</li>
                <li><strong>Communication:</strong> Keep the driver informed of any changes or delays</li>
            </ul>
            
            <p style="text-align: center;">
                <a href="<?= site_url('merchant/orders/view/' . $order['id']) ?>" class="button">Manage Order</a>
                <a href="<?= site_url('merchant/dashboard') ?>" class="button" style="background: #16a34a;">Go to Dashboard</a>
            </p>
            
            <p><strong>Important:</strong> This order is now your responsibility. Please provide excellent service to maintain your merchant rating and reputation on the platform.</p>
        </div>
        
        <div class="footer">
            <p>This email was sent by Truckers Africa</p>
            <p>Thank you for being a valued merchant partner!</p>
            <p><a href="<?= site_url() ?>">Visit Truckers Africa</a></p>
        </div>
    </div>
</body>
</html>
