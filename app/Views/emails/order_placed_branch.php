<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Received</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0e2140; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .order-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .branch-info { background: #e0f2fe; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #0e2140; }
        .button { display: inline-block; background: #2f855a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .button.reject { background: #dc2626; }
        .button.view { background: #0e2140; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Order Received!</h1>
            <p>Truckers Africa - Branch Manager</p>
        </div>
        
        <div class="content">
            <h2>Hello <?= esc($branchUser['full_name']) ?>,</h2>
            
            <p>Your branch has received a new order from a driver. Please review the details below and respond promptly.</p>
            
            <div class="branch-info">
                <h3>Your Branch</h3>
                <p><strong>Business:</strong> <?= esc($branchUser['business_name']) ?></p>
                <p><strong>Branch Location:</strong> <?= esc($branchUser['location_name']) ?></p>
            </div>
            
            <div class="order-details">
                <h3>Order Details</h3>
                <p><strong>Order ID:</strong> #<?= esc($order['booking_reference']) ?></p>
                <p><strong>Driver:</strong> <?= esc($driver_name) ?></p>
                <?php if (!empty($driver['contact_number'])): ?>
                <p><strong>Driver Contact:</strong> <?= esc($driver['contact_number']) ?></p>
                <?php elseif (!empty($driver['phone_number'])): ?>
                <p><strong>Driver Contact:</strong> <?= esc($driver['phone_number']) ?></p>
                <?php elseif (!empty($driver['phone'])): ?>
                <p><strong>Driver Contact:</strong> <?= esc($driver['phone']) ?></p>
                <?php endif; ?>
                <p><strong>Total Amount:</strong> R<?= number_format($order['grand_total'], 2) ?></p>
                <p><strong>Order Date:</strong> <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
                
                <?php if (!empty($order['vehicle_model'])): ?>
                <p><strong>Vehicle:</strong> <?= esc($order['vehicle_model']) ?> (<?= esc($order['vehicle_license_plate']) ?>)</p>
                <?php endif; ?>
                
                <?php if (!empty($order['estimated_arrival'])): ?>
                <p><strong>Estimated Arrival:</strong> <?= date('F j, Y \a\t g:i A', strtotime($order['estimated_arrival'])) ?></p>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?= site_url('branch/orders/accept/' . $order['id']) ?>" class="button">Accept Order</a>
                <a href="<?= site_url('branch/orders/reject/' . $order['id']) ?>" class="button reject">Decline Order</a>
            </div>
            
            <p>You can also view the full order details and manage it from your branch dashboard:</p>
            <p style="text-align: center;">
                <a href="<?= site_url('branch/orders/view/' . $order['id']) ?>" class="button view">View Order Details</a>
            </p>
            
            <p><strong>Important:</strong> Please respond to this order as soon as possible. Drivers are waiting for your confirmation to proceed with their journey.</p>
            
            <p style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 5px;">
                <strong>📱 Quick Access:</strong> Log in to your branch dashboard at <a href="<?= site_url('branch/login') ?>"><?= site_url('branch/login') ?></a>
            </p>
        </div>
        
        <div class="footer">
            <p>This email was sent by Truckers Africa</p>
            <p>If you have any questions, please contact your business owner or our support team.</p>
            <p><a href="<?= site_url() ?>">Visit Truckers Africa</a></p>
        </div>
    </div>
</body>
</html>

