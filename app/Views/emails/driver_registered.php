<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registration Received</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f6f8fb;margin:0;padding:0}
    .container{max-width:640px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.06)}
    .header{padding:24px;text-align:center;border-bottom:1px solid #eee}
    .content{padding:24px 32px;color:#333}
    .footer{padding:16px;text-align:center;font-size:12px;color:#777;background:#f9fafb}
    .btn{display:inline-block;background:#2563eb;color:#fff!important;padding:12px 18px;border-radius:6px;text-decoration:none;font-weight:600}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" style="height:56px;width:auto" />
    </div>
    <div class="content">
      <h2 style="margin-top:0">Welcome, <?= esc($driver['name'] ?? 'there') ?>!</h2>
      <p>Your driver account has been created successfully.</p>
      <p style="margin:24px 0;">
        <a class="btn" href="<?= site_url('login') ?>">Log in</a>
      </p>
      <p>Safe travels!</p>
    </div>
    <div class="footer">&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</div>
  </div>
</body>
</html>


