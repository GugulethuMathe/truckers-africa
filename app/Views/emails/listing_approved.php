<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listing Approved</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f6f8fb; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 24px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .header { padding: 24px; text-align: center; border-bottom: 1px solid #eee; background-color: #0e2140; }
        .header img { height: 56px; width: auto; filter: brightness(0) invert(1); }
        .content { padding: 24px 32px; color: #333; line-height: 1.6; }
        .listing-box { background-color: #f9fafb; padding: 20px; margin: 20px 0; border-left: 4px solid #0e2140; border-radius: 6px; }
        .listing-title { font-size: 18px; font-weight: bold; color: #0e2140; margin-bottom: 12px; }
        .listing-details { color: #555; font-size: 14px; }
        .listing-details p { margin: 8px 0; }
        .status-live { color: #10B981; font-weight: bold; }
        .btn { display: inline-block; background: #0e2140; color: #fff !important; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; margin: 16px 0; }
        .btn:hover { background: #1a3a5f; }
        .footer { padding: 16px; text-align: center; font-size: 12px; color: #777; background: #f9fafb; }
        .success-icon { font-size: 48px; text-align: center; margin: 16px 0; }
        ul { padding-left: 20px; }
        ul li { margin: 8px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa" />
        </div>

        <div class="content">
            <h2 style="margin-top: 0; color: #0e2140;">Your Listing Has Been Approved 🎉</h2>

            <p>Hi <?= esc($merchant['owner_name'] ?? $merchant['business_name'] ?? 'there') ?>,</p>

            <div class="success-icon">✅</div>

            <p><strong>Great news!</strong> Your service listing has been approved and is now live on the Truckers Africa platform.</p>

            <div class="listing-box">
                <div class="listing-title"><?= esc($listing['title']) ?></div>
                <div class="listing-details">
                    <p><strong>Listing ID:</strong> #<?= esc($listing['id']) ?></p>
                    <p><strong>Business:</strong> <?= esc($merchant['business_name']) ?></p>
                    <?php if (!empty($listing['price']) && $listing['price'] > 0): ?>
                        <p><strong>Price:</strong> <?= esc($listing['currency_code'] ?? 'ZAR') ?> <?= number_format($listing['price'], 2) ?></p>
                    <?php endif; ?>
                    <p><strong>Status:</strong> <span class="status-live">LIVE</span></p>
                </div>
            </div>

            <p><strong>What happens next?</strong></p>
            <ul>
                <li>Your listing is now visible to all truck drivers on the platform</li>
                <li>Drivers can view your service details and contact you</li>
                <li>You'll receive notifications when drivers show interest in your service</li>
                <li>You can manage your listing anytime from your merchant dashboard</li>
            </ul>

            <p style="text-align: center; margin: 24px 0;">
                <a class="btn" href="<?= site_url('merchant/listings') ?>">View My Listings</a>
            </p>

            <p>Thank you for being part of the Truckers Africa community. We're excited to help you connect with truck drivers across Africa!</p>

            <?php if (!empty($approved_by)): ?>
                <p style="font-size: 12px; color: #777; margin-top: 20px;">
                    <em>Approved by: <?= esc($approved_by) ?></em>
                </p>
            <?php endif; ?>

            <p style="margin-top: 24px;">
                Welcome aboard,<br/>
                <strong>Truckers Africa Team</strong>
            </p>
        </div>

        <div class="footer">
            &copy; <?= date('Y') ?> Truckers Africa. All rights reserved.
        </div>
    </div>
</body>
</html>

