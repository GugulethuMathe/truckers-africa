<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Welcome to Truckers Africa - Merchant Account Created</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f6f8fb;margin:0;padding:0}
    .container{max-width:640px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.06)}
    .header{padding:24px;text-align:center;border-bottom:1px solid #eee;background:#0e2140}
    .content{padding:24px 32px;color:#333}
    .footer{padding:16px;text-align:center;font-size:12px;color:#777;background:#f9fafb}
    .btn{display:inline-block;background:#0e2140;color:#fff!important;padding:12px 18px;border-radius:6px;text-decoration:none;font-weight:600}
    .btn:hover{background:#1a3a5f}
    .credentials-box{background:#f0f4f8;border:1px solid #0e2140;border-radius:6px;padding:16px;margin:16px 0}
    .credential-item{margin:8px 0;font-family:monospace;font-size:14px}
    .warning{background:#fef3cd;border:1px solid #fde68a;color:#92400e;padding:12px;border-radius:6px;margin:16px 0}
    .merchant-badge{background:#0e2140;color:#fff;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" style="height:56px;width:auto;filter:brightness(0) invert(1)" />
      <div style="margin-top:8px;">
        <span class="merchant-badge">MERCHANT ACCOUNT</span>
      </div>
    </div>
    <div class="content">
      <h2 style="margin-top:0">Welcome to Truckers Africa!</h2>
      
      <p>Hello <?= esc($merchant['owner_name'] ?? 'Merchant') ?>,</p>
      
      <p>Your merchant account for <strong><?= esc($merchant['business_name'] ?? 'your business') ?></strong> has been created by our admin team. You can now start receiving orders from drivers across Africa!</p>
      
      <div class="credentials-box">
        <h3 style="margin-top:0;color:#0e2140;">Your Login Credentials</h3>
        <div class="credential-item">
          <strong>Email:</strong> <?= esc($merchant['email']) ?>
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
      
      <h3>What you can do with your merchant account:</h3>
      <ul>
        <li>Create and manage your service listings</li>
        <li>Receive and respond to orders from drivers</li>
        <li>Manage your business profile and information</li>
        <li>Track your sales and order history</li>
        <li>Communicate with customers</li>
        <li>Update your business hours and availability</li>
      </ul>
      
      <h3>Next Steps:</h3>
      <ol>
        <li>Log in to your account using the credentials above</li>
        <li>Complete your business profile</li>
        <li>Add your services and products</li>
        <li>Start receiving orders from drivers!</li>
      </ol>
      
      <p>If you have any questions or need assistance setting up your account, please don't hesitate to contact our support team.</p>
      
      <p>We're excited to have you as part of the Truckers Africa community!</p>
      
      <p>Best regards,<br/>The Truckers Africa Team</p>
    </div>
    <div class="footer">&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</div>
  </div>
</body>
</html>
