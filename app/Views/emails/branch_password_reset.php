<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Branch Password Reset Request</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f6f8fb;margin:0;padding:0}
    .container{max-width:640px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.06)}
    .header{padding:24px;text-align:center;border-bottom:1px solid #eee;background:#0e2140}
    .header img{height:56px;width:auto;filter:brightness(0) invert(1)}
    .content{padding:24px 32px;color:#333}
    .footer{padding:16px;text-align:center;font-size:12px;color:#777;background:#f9fafb}
    .btn{display:inline-block;background:#10b981;color:#fff!important;padding:12px 18px;border-radius:6px;text-decoration:none;font-weight:600}
    .btn:hover{background:#059669}
    .warning{background:#fef3cd;border:1px solid #fde68a;color:#92400e;padding:12px;border-radius:6px;margin:16px 0}
    .branch-badge{background:#10b981;color:#fff;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" />
      <div style="margin-top:8px;">
        <span class="branch-badge">BRANCH ACCOUNT</span>
      </div>
    </div>
    <div class="content">
      <h2 style="margin-top:0;color:#0e2140">Password Reset Request</h2>
      
      <p>Hello <?= esc($full_name) ?>,</p>
      
      <p>We received a request to reset your password for your Truckers Africa branch account.</p>
      
      <p>Click the button below to reset your password:</p>
      
      <p style="margin:24px 0;text-align:center;">
        <a class="btn" href="<?= esc($reset_link) ?>">Reset Password</a>
      </p>
      
      <div class="warning">
        <strong>Important:</strong> This link will expire in 1 hour. If you didn't request this password reset, please ignore this email and your password will remain unchanged.
      </div>
      
      <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
      <p style="word-break:break-all;color:#666;font-size:14px;"><?= esc($reset_link) ?></p>
      
      <p>If you have any questions or need assistance, please contact your business owner or our support team.</p>
      
      <p>Best regards,<br/>The Truckers Africa Team</p>
    </div>
    <div class="footer">&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</div>
  </div>
</body>
</html>
