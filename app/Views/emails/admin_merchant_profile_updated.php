<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Merchant Profile Updated</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f6f8fb;margin:0;padding:0}
    .container{max-width:640px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.06)}
    .header{padding:24px;text-align:center;border-bottom:1px solid #eee;background:#0e2140}
    .header img{height:56px;width:auto;filter:brightness(0) invert(1)}
    .content{padding:24px 32px;color:#333}
    .footer{padding:16px;text-align:center;font-size:12px;color:#777;background:#f9fafb}
    .btn{display:inline-block;background:#0e2140;color:#fff!important;padding:12px 18px;border-radius:6px;text-decoration:none;font-weight:600}
    .btn:hover{background:#1a3a5f}
    .info-box{background:#f0f4f8;border-left:4px solid #0e2140;padding:15px;margin:16px 0;border-radius:4px}
    .info-item{margin:8px 0;font-size:14px}
    .info-label{font-weight:600;color:#0e2140;display:inline-block;min-width:150px}
    .badge{display:inline-block;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;margin:4px 0}
    .badge-success{background:#d1fae5;color:#065f46}
    .badge-warning{background:#fef3c7;color:#92400e}
    .image-preview{max-width:120px;height:120px;object-fit:cover;border-radius:8px;border:2px solid #0e2140;margin:8px 0}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" />
    </div>
    <div class="content">
      <h2 style="margin-top:0;color:#0e2140">📝 Merchant Profile Updated</h2>
      
      <p>A merchant has completed their profile during onboarding:</p>
      
      <div class="info-box">
        <h3 style="margin-top:0;color:#0e2140">Business Information</h3>
        
        <div class="info-item">
          <span class="info-label">Business Name:</span>
          <strong><?= esc($merchant['business_name'] ?? 'N/A') ?></strong>
        </div>
        
        <div class="info-item">
          <span class="info-label">Owner Name:</span>
          <?= esc($merchant['owner_name'] ?? 'N/A') ?>
        </div>
        
        <div class="info-item">
          <span class="info-label">Email:</span>
          <a href="mailto:<?= esc($merchant['email']) ?>" style="color:#0e2140"><?= esc($merchant['email']) ?></a>
        </div>
        
        <div class="info-item">
          <span class="info-label">Contact Number:</span>
          <?= esc($merchant['business_contact_number'] ?? 'N/A') ?>
        </div>
        
        <?php if (!empty($merchant['business_whatsapp_number'])): ?>
        <div class="info-item">
          <span class="info-label">WhatsApp:</span>
          <?= esc($merchant['business_whatsapp_number']) ?>
        </div>
        <?php endif; ?>
        
        <div class="info-item">
          <span class="info-label">Physical Address:</span>
          <?= esc($merchant['physical_address'] ?? 'N/A') ?>
        </div>
        
        <div class="info-item">
          <span class="info-label">Main Service:</span>
          <?= esc($merchant['main_service'] ?? 'N/A') ?>
        </div>
        
        <div class="info-item">
          <span class="info-label">Status:</span>
          <?php if (($merchant['verification_status'] ?? '') === 'approved'): ?>
            <span class="badge badge-success">✓ Approved</span>
          <?php else: ?>
            <span class="badge badge-warning">⏳ Pending</span>
          <?php endif; ?>
        </div>
      </div>
      
      <?php if (!empty($merchant['business_description'])): ?>
      <div class="info-box">
        <h3 style="margin-top:0;color:#0e2140">Business Description</h3>
        <p style="margin:0;white-space:pre-wrap"><?= esc($merchant['business_description']) ?></p>
      </div>
      <?php endif; ?>
      
      <?php if (!empty($merchant['profile_description'])): ?>
      <div class="info-box">
        <h3 style="margin-top:0;color:#0e2140">Additional Information</h3>
        <p style="margin:0;white-space:pre-wrap"><?= esc($merchant['profile_description']) ?></p>
      </div>
      <?php endif; ?>
      
      <?php if (!empty($merchant['profile_image_url']) || !empty($merchant['business_image_url'])): ?>
      <div class="info-box">
        <h3 style="margin-top:0;color:#0e2140">Uploaded Images</h3>
        <div style="display:flex;gap:16px;flex-wrap:wrap">
          <?php if (!empty($merchant['profile_image_url'])): ?>
          <div>
            <p style="margin:0 0 8px 0;font-size:12px;color:#666">Profile Photo:</p>
            <img src="<?= base_url(esc($merchant['profile_image_url'])) ?>" alt="Profile Photo" class="image-preview" style="border-radius:50%">
          </div>
          <?php endif; ?>
          
          <?php if (!empty($merchant['business_image_url'])): ?>
          <div>
            <p style="margin:0 0 8px 0;font-size:12px;color:#666">Business Image:</p>
            <img src="<?= base_url(esc($merchant['business_image_url'])) ?>" alt="Business Image" class="image-preview">
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
      
      <p style="margin:24px 0;text-align:center;">
        <a class="btn" href="<?= site_url('admin/merchants/view/' . ($merchant['id'] ?? '')) ?>">View Full Profile</a>
      </p>
      
      <p style="font-size:14px;color:#666;margin-top:24px">
        <strong>Next Steps:</strong><br>
        • Review the merchant's profile information<br>
        • Verify business details if needed<br>
        • Monitor their onboarding progress<br>
        • Merchant will select a subscription plan next
      </p>
      
      <p style="font-size:12px;color:#999;margin-top:16px">
        This notification was sent because a merchant completed their profile during the onboarding process.
      </p>
    </div>
    <div class="footer">&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</div>
  </div>
</body>
</html>

