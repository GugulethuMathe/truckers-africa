<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merchant Account Approved</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f6f8fb; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 24px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .header { padding: 24px; text-align: center; border-bottom: 1px solid #eee; background-color: #0e2140; }
        .header img { height: 56px; width: auto; filter: brightness(0) invert(1); }
        .content { padding: 24px 32px; color: #333; }
        .btn { display: inline-block; background: #0e2140; color: #fff !important; padding: 12px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; }
        .btn:hover { background: #1a3a5f; }
        .footer { padding: 16px; text-align: center; font-size: 12px; color: #777; background: #f9fafb; }
    </style>
    </head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" />
        </div>
        <div class="content">
            <h2 style="margin-top: 0; color: #0e2140;">Your merchant account is approved 🎉</h2>
            <p>Hi <?= esc($merchant['owner_name'] ?? $merchant['business_name'] ?? 'there') ?>,</p>
            <p>Great news! Your Truckers Africa merchant account has been approved<?= $approved_by ? ' by ' . esc($approved_by) : '' ?>.</p>
            <p>You can now access your dashboard, publish listings, and receive orders from drivers.</p>
            <p style="margin: 24px 0;">
                <a class="btn" href="<?= site_url('merchant/dashboard') ?>">Go to Dashboard</a>
            </p>
            <p>If you have any questions, reply to this email and our team will assist.</p>
            <p>Welcome aboard,<br/>Truckers Africa Team</p>
        </div>
        <div class="footer">
            &copy; <?= date('Y') ?> Truckers Africa. All rights reserved.
        </div>
    </div>
</body>
</html>


