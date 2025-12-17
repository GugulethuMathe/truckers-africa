<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Update</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f59e0b; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .order-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .reason-box { background: #fef2f2; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #ef4444; }
        .suggestions { background: #f0f9ff; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #0ea5e9; }
        .button { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .button.primary { background: #16a34a; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Update</h1>
            <p>Truckers Africa</p>
        </div>
        
        <div class="content">
            <h2>Hello <?= esc($driver['name']) ?>,</h2>
            
            <p>We have an update regarding your recent order. Unfortunately, <?= esc($merchant['business_name']) ?> is unable to fulfill your order at this time.</p>
            
            <div class="order-details">
                <h3>Order Information</h3>
                <p><strong>Order ID:</strong> #<?= esc($order['booking_reference']) ?></p>
                <p><strong>Merchant:</strong> <?= esc($merchant['business_name']) ?></p>
                <p><strong>Total Amount:</strong> R<?= number_format($order['grand_total'], 2) ?></p>
                <p><strong>Status:</strong> <span style="color: #ef4444; font-weight: bold;">Unable to Fulfill</span></p>
                <p><strong>Order Date:</strong> <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
            </div>
            
            <?php if (!empty($rejection_reason)): ?>
            <div class="reason-box">
                <h3>Reason Provided:</h3>
                <p><?= esc($rejection_reason) ?></p>
            </div>
            <?php endif; ?>
            
            <div class="suggestions">
                <h3>💡 What you can do next:</h3>
                <ul>
                    <li><strong>Try Other Merchants:</strong> Browse similar services from other merchants in your area</li>
                    <li><strong>Adjust Your Route:</strong> Consider modifying your route to include more merchant options</li>
                    <li><strong>Contact Directly:</strong> Reach out to the merchant directly to see if they can accommodate you later</li>
                    <li><strong>Try Again Later:</strong> The merchant might be available at a different time</li>
                </ul>
            </div>
            
            <h3>Don't worry - we're here to help!</h3>
            <p>There are many other great merchants on Truckers Africa who would be happy to serve you. Use our search and filtering features to find exactly what you need.</p>
            
            <p style="text-align: center;">
                <a href="<?= site_url('driver/dashboard') ?>" class="button primary">Browse Merchants</a>
                <a href="<?= site_url('search') ?>" class="button">Search Services</a>
                <a href="<?= site_url('driver/orders/view/' . $order['id']) ?>" class="button">View Order Details</a>
            </p>
            
            <p><strong>Need Help?</strong> Our support team is here to assist you in finding the right services for your journey. Don't hesitate to reach out!</p>
            
            <div style="background: #e0f2fe; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center;">
                <p style="margin: 0; font-weight: bold; color: #0284c7;">Remember: This doesn't reflect on you as a driver!</p>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #0369a1;">Merchants sometimes can't fulfill orders due to availability, capacity, or other factors.</p>
            </div>
        </div>
        
        <div class="footer">
            <p>This email was sent by Truckers Africa</p>
            <p>We're committed to helping you find the best services for your journey!</p>
            <p><a href="<?= site_url() ?>">Visit Truckers Africa</a></p>
        </div>
    </div>
</body>
</html>
