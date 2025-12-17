<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Rejection Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #ef4444; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .order-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .reason-box { background: #fef2f2; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #ef4444; }
        .tips { background: #f0f9ff; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #0ea5e9; }
        .button { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Rejection Confirmed</h1>
            <p>Truckers Africa</p>
        </div>
        
        <div class="content">
            <h2>Hello <?= esc($merchant['business_name']) ?>,</h2>
            
            <p>This confirms that you have declined the order from <?= esc($driver['name'] . ' ' . $driver['surname']) ?>. The driver has been notified of your decision.</p>
            
            <div class="order-details">
                <h3>Declined Order Details</h3>
                <p><strong>Order ID:</strong> #<?= esc($order['booking_reference']) ?></p>
                <p><strong>Driver:</strong> <?= esc($driver['name'] . ' ' . $driver['surname']) ?></p>
                <p><strong>Total Amount:</strong> R<?= number_format($order['grand_total'], 2) ?></p>
                <p><strong>Order Date:</strong> <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
                <p><strong>Declined On:</strong> <?= date('F j, Y \a\t g:i A') ?></p>
                <p><strong>Status:</strong> <span style="color: #ef4444; font-weight: bold;">Declined</span></p>
            </div>
            
            <?php if (!empty($rejection_reason)): ?>
            <div class="reason-box">
                <h3>Reason You Provided:</h3>
                <p><?= esc($rejection_reason) ?></p>
                <p style="font-size: 14px; color: #7f1d1d; margin: 10px 0 0 0;">This reason has been shared with the driver to help them understand your decision.</p>
            </div>
            <?php endif; ?>
            
            <div class="tips">
                <h3>💡 Tips for Future Orders:</h3>
                <ul>
                    <li><strong>Update Availability:</strong> Keep your service availability updated to reduce unwanted orders</li>
                    <li><strong>Clear Descriptions:</strong> Ensure your service descriptions clearly state any limitations</li>
                    <li><strong>Quick Response:</strong> Respond to orders quickly to maintain good merchant ratings</li>
                    <li><strong>Professional Communication:</strong> Always provide clear reasons when declining orders</li>
                </ul>
            </div>
            
            <h3>What happens now?</h3>
            <p>The driver will be encouraged to:</p>
            <ul>
                <li>Browse other merchants who might be able to help them</li>
                <li>Adjust their route or timing if possible</li>
                <li>Contact you directly if they want to discuss alternatives</li>
            </ul>
            
            <p style="text-align: center;">
                <a href="<?= site_url('merchant/orders/all') ?>" class="button">View All Orders</a>
                <a href="<?= site_url('merchant/dashboard') ?>" class="button" style="background: #16a34a;">Go to Dashboard</a>
            </p>
            
            <div style="background: #fef3c7; padding: 15px; margin: 20px 0; border-radius: 5px;">
                <p style="margin: 0; font-weight: bold; color: #92400e;">Remember:</p>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #92400e;">While it's okay to decline orders you can't fulfill, frequent rejections may impact your merchant rating. Consider updating your service availability or descriptions to better match driver expectations.</p>
            </div>
        </div>
        
        <div class="footer">
            <p>This email was sent by Truckers Africa</p>
            <p>Thank you for being a responsible merchant partner!</p>
            <p><a href="<?= site_url() ?>">Visit Truckers Africa</a></p>
        </div>
    </div>
</body>
</html>
