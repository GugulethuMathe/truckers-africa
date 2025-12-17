<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Document Uploaded</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f6f8fb; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 24px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .header { padding: 24px; text-align: center; border-bottom: 1px solid #eee; background-color: #0e2140; }
        .header img { height: 56px; width: auto; filter: brightness(0) invert(1); }
        .content { padding: 24px 32px; color: #333; line-height: 1.6; }
        .document-box { background-color: #f9fafb; padding: 20px; margin: 20px 0; border-left: 4px solid #0e2140; border-radius: 6px; }
        .document-type { font-size: 18px; font-weight: bold; color: #0e2140; margin-bottom: 12px; }
        .merchant-details { color: #555; font-size: 14px; }
        .merchant-details p { margin: 8px 0; }
        .btn { display: inline-block; background: #0e2140; color: #fff !important; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; margin: 16px 0; }
        .btn:hover { background: #1a3a5f; }
        .footer { padding: 16px; text-align: center; font-size: 12px; color: #777; background: #f9fafb; }
        .alert-icon { font-size: 48px; text-align: center; margin: 16px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" />
        </div>
        
        <div class="content">
            <h2 style="margin-top: 0; color: #0e2140;">New Document Uploaded for Review</h2>
            
            <p>Hello Admin,</p>
            
            <div class="alert-icon">📄</div>
            
            <p>A merchant has uploaded a new document that requires your review and approval.</p>
            
            <div class="document-box">
                <div class="document-type"><?= esc($document_type) ?></div>
                <div class="merchant-details">
                    <p><strong>Business Name:</strong> <?= esc($merchant['business_name']) ?></p>
                    <p><strong>Owner Name:</strong> <?= esc($merchant['owner_name']) ?></p>
                    <p><strong>Email:</strong> <?= esc($merchant['email']) ?></p>
                    <p><strong>Phone:</strong> <?= esc($merchant['business_contact_number'] ?? 'N/A') ?></p>
                    <p><strong>Document ID:</strong> #<?= esc($document_id) ?></p>
                </div>
            </div>
            
            <p><strong>Action Required:</strong></p>
            <ul>
                <li>Review the uploaded document</li>
                <li>Verify it meets the requirements</li>
                <li>Approve or reject the document</li>
            </ul>
            
            <p style="text-align: center; margin: 24px 0;">
                <a class="btn" href="<?= site_url('admin/merchants/view/' . $merchant['id']) ?>">Review Document</a>
            </p>
            
            <p style="margin-top: 24px;">
                Best regards,<br/>
                <strong>Truckers Africa System</strong>
            </p>
        </div>
        
        <div class="footer">
            &copy; <?= date('Y') ?> Truckers Africa. All rights reserved.
        </div>
    </div>
</body>
</html>

