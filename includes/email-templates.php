<?php
/** Wraps email body content in a clean, branded, table-based HTML layout (email-client safe). */
function auditEmailWrap(string $preheader, string $bodyHtml): string {
    $year = date('Y');
    return '
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<span style="display:none;font-size:1px;color:#f1f5f9;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">' . htmlspecialchars($preheader) . '</span>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px;">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.06);">

<tr><td style="background:#16a34a;padding:22px 32px;">
<span style="color:#ffffff;font-size:17px;font-weight:700;letter-spacing:-0.2px;">Tedmark Digital</span>
</td></tr>

<tr><td style="padding:36px 32px 28px;">
' . $bodyHtml . '
</td></tr>

<tr><td style="background:#f8fafc;padding:20px 32px;border-top:1px solid #eceef1;">
<p style="margin:0;color:#94a3b8;font-size:12px;line-height:1.6;">
Tedmark Digital Agency &middot; Accra, Ghana<br>
&copy; ' . $year . ' Tedmark Digital Agency. This is an automated message from our free Website Audit tool.
</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>';
}

function auditEmailButton(string $text, string $url, string $color = '#16a34a'): string {
    return '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0;"><tr><td style="background:' . $color . ';border-radius:10px;">
<a href="' . htmlspecialchars($url) . '" style="display:inline-block;padding:14px 28px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">' . htmlspecialchars($text) . '</a>
</td></tr></table>';
}
