<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Password Reset Request</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f6f8fb;margin:0;padding:0}
    .container{max-width:640px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.06)}
    .header{padding:24px;text-align:center;border-bottom:1px solid #eee;background:#0e2140}
    .header img{height:56px;width:auto;filter:brightness(0) invert(1)}
    .content{padding:24px 32px;color:#333}
    .footer{padding:16px;text-align:center;font-size:12px;color:#777;background:#f9fafb}
    .btn{display:inline-block;background:#0e2140;color:#fff!important;padding:12px 18px;border-radius:6px;text-decoration:none;font-weight:600}
    .btn:hover{background:#1a3a5f}
    .warning{background:#fef3cd;border:1px solid #fde68a;color:#92400e;padding:12px;border-radius:6px;margin:16px 0}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" />
    </div>
    <div class="content">
      <h2 style="margin-top:0;color:#0e2140">Password Reset Request</h2>
      
      <?php if ($user_type === 'driver'): ?>
        <p>Hello <?= esc($user['name'] ?? 'Driver') ?>,</p>
      <?php elseif ($user_type === 'merchant'): ?>
        <p>Hello <?= esc($user['owner_name'] ?? 'Merchant') ?>,</p>
      <?php endif; ?>
      
      <p>We received a request to reset your password for your Truckers Africa account.</p>
      
      <p>Click the button below to reset your password:</p>
      
      <p style="margin:24px 0;text-align:center;">
        <a class="btn" href="<?= esc($reset_url) ?>">Reset Password</a>
      </p>
      
      <div class="warning">
        <strong>Important:</strong> This link will expire in <?= esc($token_expires) ?>. If you didn't request this password reset, please ignore this email.
      </div>
      
      <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
      <p style="word-break:break-all;color:#666;font-size:14px;"><?= esc($reset_url) ?></p>
      
      <p>If you have any questions, please contact our support team.</p>
      
      <p>Best regards,<br/>The Truckers Africa Team</p>
    </div>
    <div class="footer">&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</div>
  </div>
</body>
</html>
