<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Welcome to Truckers Africa - Driver Account Created</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f6f8fb;margin:0;padding:0}
    .container{max-width:640px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.06)}
    .header{padding:24px;text-align:center;border-bottom:1px solid #eee}
    .content{padding:24px 32px;color:#333}
    .footer{padding:16px;text-align:center;font-size:12px;color:#777;background:#f9fafb}
    .btn{display:inline-block;background:#2563eb;color:#fff!important;padding:12px 18px;border-radius:6px;text-decoration:none;font-weight:600}
    .credentials-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:16px;margin:16px 0}
    .credential-item{margin:8px 0;font-family:monospace;font-size:14px}
    .warning{background:#fef3cd;border:1px solid #fde68a;color:#92400e;padding:12px;border-radius:6px;margin:16px 0}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" style="height:56px;width:auto" />
    </div>
    <div class="content">
      <h2 style="margin-top:0">Welcome to Truckers Africa!</h2>
      
      <p>Hello <?= esc($driver['name'] ?? 'Driver') ?>,</p>
      
      <p>Your driver account has been created by our admin team. You can now access the Truckers Africa platform to find and book services from verified merchants.</p>
      
      <div class="credentials-box">
        <h3 style="margin-top:0;color:#1f2937;">Your Login Credentials</h3>
        <div class="credential-item">
          <strong>Email:</strong> <?= esc($driver['email']) ?>
        </div>
        <div class="credential-item">
          <strong>Password:</strong> <?= esc($password) ?>
        </div>
      </div>
      
      <div class="warning">
        <strong>Important:</strong> Please change your password after your first login for security purposes.
      </div>
      
      <p style="margin:24px 0;text-align:center;">
        <a class="btn" href="<?= esc($login_url) ?>">Login to Your Account</a>
      </p>
      
      <h3>What you can do with your account:</h3>
      <ul>
        <li>Search for services and merchants in your area</li>
        <li>Place orders for truck services and parts</li>
        <li>Track your order status</li>
        <li>Manage your profile and preferences</li>
        <li>View your order history</li>
      </ul>
      
      <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
      
      <p>Welcome aboard and safe travels!</p>
      
      <p>Best regards,<br/>The Truckers Africa Team</p>
    </div>
    <div class="footer">&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</div>
  </div>
</body>
</html>
