<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Approved</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f6f8fb; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 24px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .header { padding: 24px; text-align: center; border-bottom: 1px solid #eee; background-color: #0e2140; }
        .header img { height: 56px; width: auto; filter: brightness(0) invert(1); }
        .content { padding: 24px 32px; color: #333; line-height: 1.6; }
        .document-box { background-color: #f0fdf4; padding: 20px; margin: 20px 0; border-left: 4px solid #10B981; border-radius: 6px; }
        .document-type { font-size: 18px; font-weight: bold; color: #10B981; margin-bottom: 12px; }
        .status-approved { color: #10B981; font-weight: bold; }
        .btn { display: inline-block; background: #0e2140; color: #fff !important; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; margin: 16px 0; }
        .btn:hover { background: #1a3a5f; }
        .footer { padding: 16px; text-align: center; font-size: 12px; color: #777; background: #f9fafb; }
        .success-icon { font-size: 48px; text-align: center; margin: 16px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" />
        </div>
        
        <div class="content">
            <h2 style="margin-top: 0; color: #0e2140;">Document Approved ✓</h2>
            
            <p>Hi <?= esc($merchant['owner_name'] ?? $merchant['business_name'] ?? 'there') ?>,</p>
            
            <div class="success-icon">✅</div>
            
            <p><strong>Good news!</strong> Your document has been reviewed and approved.</p>
            
            <div class="document-box">
                <div class="document-type"><?= esc($document_type) ?></div>
                <p style="margin: 8px 0; color: #555;">
                    <strong>Status:</strong> <span class="status-approved">APPROVED</span>
                </p>
            </div>
            
            <p><strong>What's next?</strong></p>
            <ul>
                <li>Your document has been verified and accepted</li>
                <li>Continue uploading any remaining required documents</li>
                <li>Once all documents are approved, your account will be fully verified</li>
            </ul>
            
            <p style="text-align: center; margin: 24px 0;">
                <a class="btn" href="<?= site_url('merchant/verification') ?>">View Verification Status</a>
            </p>
            
            <?php if (!empty($approved_by)): ?>
                <p style="font-size: 12px; color: #777; margin-top: 20px;">
                    <em>Approved by: <?= esc($approved_by) ?></em>
                </p>
            <?php endif; ?>
            
            <p style="margin-top: 24px;">
                Thank you for your cooperation,<br/>
                <strong>Truckers Africa Team</strong>
            </p>
        </div>
        
        <div class="footer">
            &copy; <?= date('Y') ?> Truckers Africa. All rights reserved.
        </div>
    </div>
</body>
</html>

