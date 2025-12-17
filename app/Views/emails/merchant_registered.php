<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registration Received</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f6f8fb;margin:0;padding:0}
    .container{max-width:640px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.06)}
    .header{padding:24px;text-align:center;border-bottom:1px solid #eee;background-color:#0e2140}
    .header img{height:56px;width:auto;filter:brightness(0) invert(1)}
    .content{padding:24px 32px;color:#333}
    .footer{padding:16px;text-align:center;font-size:12px;color:#777;background:#f9fafb}
    .btn{display:inline-block;background:#0e2140;color:#fff!important;padding:12px 18px;border-radius:6px;text-decoration:none;font-weight:600}
    .btn:hover{background:#1a3a5f}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" />
    </div>
    <div class="content">
      <h2 style="margin-top:0;color:#0e2140">Thanks for registering, <?= esc($merchant['owner_name'] ?? $merchant['business_name'] ?? 'there') ?>!</h2>
      <p>Your merchant account is pending review. We’ll notify you once it’s approved.</p>
      <p>You can start preparing your profile while you wait.</p>
      <p style="margin:24px 0;">
        <a class="btn" href="<?= site_url('login') ?>">Log in</a>
      </p>
      <p>If you have questions, reply to this email.</p>
    </div>
    <div class="footer">&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</div>
  </div>
</body>
</html>


