<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Merchant Registration</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; margin: 20px 0; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 20px 0; border-bottom: 1px solid #dddddd;">
                            <img src="<?= site_url('assets/images/logo-icon-black.png') ?>" alt="Truckers Africa Logo" style="height: 60px; width: auto;">
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px 40px;">
                            <h1 style="font-size: 24px; color: #333333; margin-top: 0;">New Merchant Registration</h1>
                            <p style="font-size: 16px; color: #555555; line-height: 1.6;">
                                A new merchant has successfully registered on the Truckers Africa platform. Please review their details below and take any necessary actions in the admin panel.
                            </p>
                            <hr style="border: 0; border-top: 1px solid #dddddd; margin: 20px 0;">
                            <h2 style="font-size: 20px; color: #333333;">Merchant Details:</h2>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size: 16px; color: #555555;">
                                <tr>
                                    <td style="padding: 10px 0; font-weight: bold; width: 150px;">Full Name:</td>
                                    <td style="padding: 10px 0;"><?= esc($full_name) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; font-weight: bold;">Company Name:</td>
                                    <td style="padding: 10px 0;"><?= esc($company_name) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; font-weight: bold;">Email Address:</td>
                                    <td style="padding: 10px 0;"><?= esc($email) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; font-weight: bold;">Phone Number:</td>
                                    <td style="padding: 10px 0;"><?= esc($phone) ?></td>
                                </tr>
                            </table>
                            <p style="text-align: center; margin-top: 30px;">
                                <a href="<?= site_url('admin/login') ?>" style="background-color: #1a73e8; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">Go to Admin Panel</a>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 20px; background-color: #f8f9fa; color: #888888; font-size: 12px; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                            <p>&copy; <?= date('Y') ?> Truckers Africa. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
