<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:20px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;border-top:6px solid #28a745;box-shadow:0px 2px 10px rgba(0,0,0,0.1);">

<tr>
<td align="center" style="padding:20px;background:linear-gradient(135deg,#28a745,#1e7e34);border-radius:8px 8px 0 0;">
<img src="https://smesupport.ogunstate.gov.ng/ogunlogo.png" width="60" style="display:block;margin-bottom:10px;">
<h1 style="margin:0;color:#ffffff;font-size:22px;">Password Reset Request</h1>
</td>
</tr>

<tr>
<td style="padding:25px;font-size:15px;color:#333;line-height:1.6;">

<p>Hi {{$name}},</p>

<p>
We received a request to reset your password. Click the button below to reset it instantly.
</p>

<table cellpadding="0" cellspacing="0" align="center" style="margin:20px auto;">
<tr>
<td align="center" bgcolor="#28a745" style="border-radius:6px;">
<a href="{{$link}}" 
style="display:inline-block;padding:12px 22px;font-size:15px;color:#ffffff;text-decoration:none;font-weight:bold;">
Reset Your Password
</a>
</td>
</tr>
</table>

<p>This link expires in 60 minutes.</p>

<p>If you did not request a password reset, please ignore this email.</p>

<p>
Thanks,<br>
<strong>Ogun Social Register Team</strong>
</p>

</td>
</tr>

<tr>
<td align="center" style="font-size:12px;color:#777;padding:15px;border-top:1px solid #eaeaea;">
&copy; {{ date('Y') }} Ogun Social Register. All Rights Reserved.
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
