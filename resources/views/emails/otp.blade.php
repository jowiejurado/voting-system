<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Your One-Time Passcode (OTP)</title>
	<style>
		/* Simple, email-safe styles to resemble the provided design */
		.body{background:#f6f7fb;padding:24px;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,"Apple Color Emoji","Segoe UI Emoji";}
		.card{max-width:560px;margin:0 auto;background:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.05);overflow:hidden;}
		.header{padding:32px 32px 0 32px;text-align:center;}
		.brand{font-weight:800;letter-spacing:.12em;color:#111827;margin:8px 0 0 0;}
		.title{font-size:22px;margin:8px 0 0 0;color:#111827;}
		.content{padding:20px 32px 32px 32px;color:#374151;line-height:1.6;}
		.button{display:inline-block;background:#2563eb;color:#fff !important;text-decoration:none;padding:14px 22px;border-radius:10px;font-weight:700}
		.footer{color:#9ca3af;text-align:center;font-size:12px;padding:18px}
		.code{margin:8px 0 16px 0;font-size:28px;font-weight:800;letter-spacing:6px;text-align:center;color:#111;}
	</style>
	<!--[if mso]>
		<style>
			.button{padding:14px 22px !important;}
		</style>
	<![endif]-->
	</head>
<body class="body">
	<div class="card">
		<div class="header">
			<img src="https://voting-system-master-2w6shv.laravel.cloud/logo.png" alt="Logo" width="40" height="40" style="display:block;margin:0 auto 8px auto;border-radius:9999px" />
			<h2 class="brand">PASEI - Voting System</h2>
			<p class="title">Your One-Time Passcode (OTP)</p>
		</div>
		<div class="content">
			<p>Hello <strong>{{ $user->first_name ?? 'there' }}</strong>,</p>
			<p>Use the one-time passcode below to continue:</p>
			<p class="code">{{ $code }}</p>
			<p class="note">This code expires in 5 minutes. If you did not request this, you can ignore this email.</p>
		</div>
	</div>
	<p class="footer">© {{ date('Y') }} PASEI. All rights reserved.</p>
</body>
</html>


