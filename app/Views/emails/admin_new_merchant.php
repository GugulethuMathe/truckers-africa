<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #6366f1;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .alert {
            background-color: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
        }
        .merchant-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .detail-row {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 0.9em;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #6366f1;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .button-approve {
            background-color: #10b981;
        }
        .button-reject {
            background-color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Merchant Registration</h1>
    </div>

    <div class="content">
        <div class="alert">
            <strong>Action Required:</strong> A new merchant has registered and is awaiting approval.
        </div>

        <p>Dear Admin,</p>

        <p>A new merchant has registered on Truckers Africa and requires your review and approval.</p>

        <div class="merchant-details">
            <h3 style="color: #6366f1; margin-top: 0;">Merchant Information</h3>

            <div class="detail-row">
                <span class="label">Business Name:</span>
                <span class="value"><?= esc($business_name) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Contact Person:</span>
                <span class="value"><?= esc($contact_person) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Email:</span>
                <span class="value"><?= esc($email) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Phone:</span>
                <span class="value"><?= esc($phone) ?></span>
            </div>

            <div class="detail-row">
                <span class="label">Registration Date:</span>
                <span class="value"><?= date('F j, Y g:i A', strtotime($registration_date)) ?></span>
            </div>
        </div>

        <p>Please review the merchant's application and take appropriate action.</p>

        <center>
            <a href="<?= base_url('admin/merchants/pending') ?>" class="button">Review Application</a>
        </center>

        <p>Best regards,<br>
        <strong>Truckers Africa System</strong></p>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
        <p>This is an automated email notification.</p>
    </div>
</body>
</html>
