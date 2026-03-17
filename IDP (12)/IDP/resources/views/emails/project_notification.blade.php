<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:40px;">

<div style="max-width:600px; margin:auto; background:white; padding:30px; border-radius:8px;">

<h2 style="margin-bottom:15px;">
{{ $title }}
</h2>

<p style="color:#555; font-size:15px;">
{{ $messageText }}
</p>

@if($url)

<div style="margin-top:25px;">
<a href="{{ $url }}"
style="display:inline-block;
background:#000;
color:white;
padding:12px 20px;
text-decoration:none;
border-radius:5px;
font-size:14px;">
Open Platform
</a>
</div>

@endif

<hr style="margin-top:35px;">

<p style="font-size:12px; color:#888;">
Interior Design Portal (IDP)
Secure Milestone Escrow Platform
</p>

</div>

</body>
</html>
