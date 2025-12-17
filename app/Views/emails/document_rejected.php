<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Requires Attention</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f6f8fb; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 24px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .header { padding: 24px; text-align: center; border-bottom: 1px solid #eee; background-color: #0e2140; }
        .header img { height: 56px; width: auto; filter: brightness(0) invert(1); }
        .content { padding: 24px 32px; color: #333; line-height: 1.6; }
        .document-box { background-color: #fef2f2; padding: 20px; margin: 20px 0; border-left: 4px solid #EF4444; border-radius: 6px; }
        .document-type { font-size: 18px; font-weight: bold; color: #EF4444; margin-bottom: 12px; }
        .status-rejected { color: #EF4444; font-weight: bold; }
        .reason-box { background-color: #fff; padding: 16px; margin: 12px 0; border-radius: 4px; border: 1px solid #fee; }
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
            <h2 style="margin-top: 0; color: #0e2140;">Document Requires Attention</h2>
            
            <p>Hi <?= esc($merchant['owner_name'] ?? $merchant['business_name'] ?? 'there') ?>,</p>
            
            <div class="alert-icon">⚠️</div>
            
            <p>We've reviewed your document submission, but unfortunately it does not meet our requirements at this time.</p>
            
            <div class="document-box">
                <div class="document-type"><?= esc($document_type) ?></div>
                <p style="margin: 8px 0; color: #555;">
                    <strong>Status:</strong> <span class="status-rejected">REQUIRES RESUBMISSION</span>
                </p>
                
                <div class="reason-box">
                    <p style="margin: 0; color: #555;"><strong>Reason:</strong></p>
                    <p style="margin: 8px 0 0 0; color: #333;"><?= esc($rejection_reason) ?></p>
                </div>
            </div>
            
            <p><strong>What you need to do:</strong></p>
            <ul>
                <li>Review the rejection reason above carefully</li>
                <li>Prepare a new document that meets the requirements</li>
                <li>Upload the corrected document through your verification page</li>
                <li>Ensure the document is clear, legible, and valid</li>
            </ul>
            
            <p style="text-align: center; margin: 24px 0;">
                <a class="btn" href="<?= site_url('merchant/verification') ?>">Upload New Document</a>
            </p>
            
            <p>If you have any questions or need clarification, please don't hesitate to contact our support team.</p>
            
            <?php if (!empty($rejected_by)): ?>
                <p style="font-size: 12px; color: #777; margin-top: 20px;">
                    <em>Reviewed by: <?= esc($rejected_by) ?></em>
                </p>
            <?php endif; ?>
            
            <p style="margin-top: 24px;">
                We're here to help,<br/>
                <strong>Truckers Africa Team</strong>
            </p>
        </div>
        
        <div class="footer">
            &copy; <?= date('Y') ?> Truckers Africa. All rights reserved.
        </div>
    </div>
</body>
</html>

