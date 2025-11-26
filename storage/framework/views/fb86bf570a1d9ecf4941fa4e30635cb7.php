<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($subject ?? 'Newsletter KAZARIA'); ?></title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f6f8; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="background-color: #ff5a1f; color: #ffffff; padding: 16px 20px; text-align: center;">
                <h1 style="margin: 0; font-size: 20px;">KAZARIA</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px; color: #333333; font-size: 15px; line-height: 1.6;">
                <?php echo nl2br(e($content)); ?>

            </td>
        </tr>
        <tr>
            <td style="padding: 0 24px 24px; color: #666666; font-size: 13px;">
                <p style="margin-bottom: 8px;">Merci de faire partie de la communauté KAZARIA.</p>
                <p style="margin: 0;">À très vite !</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px; background-color: #f5f6f8; color: #999999; font-size: 12px; text-align: center;">
                Vous recevez ce message car vous vous êtes inscrit(e) à notre newsletter.
            </td>
        </tr>
    </table>
</body>
</html>

<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/emails/newsletter/broadcast.blade.php ENDPATH**/ ?>