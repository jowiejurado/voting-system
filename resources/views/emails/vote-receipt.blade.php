<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Vote receipt</title>
	<style>
		.body{background:#f6f7fb;padding:24px;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,"Apple Color Emoji","Segoe UI Emoji";}
		.card{max-width:560px;margin:0 auto;background:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.05);overflow:hidden;}
		.header{padding:32px 32px 0 32px;text-align:center;}
		.brand{font-weight:800;letter-spacing:.12em;color:#111827;margin:8px 0 0 0;}
		.title{font-size:22px;margin:8px 0 0 0;color:#111827;}
		.content{padding:20px 32px 32px 32px;color:#374151;line-height:1.6;}
		.meta{font-size:14px;color:#6b7280;margin:12px 0 20px 0;}
		.position{font-weight:700;color:#111827;margin:20px 0 6px 0;font-size:15px;}
		.choices{margin:0 0 0 18px;padding:0;}
		.choices li{margin:4px 0;}
		.skipped{color:#9ca3af;font-style:italic;}
		.footer{color:#9ca3af;text-align:center;font-size:12px;padding:18px}
	</style>
</head>
<body class="body">
	<div class="card">
		<div class="header">
			<img src="{{ $logoUrl }}" alt="Logo" width="40" height="40" style="display:block;margin:0 auto 8px auto;border-radius:9999px" />
			<h2 class="brand">PASEI - Voting System</h2>
			<p class="title">Vote receipt</p>
		</div>
		<div class="content">
			<p>Hello <strong>{{ $user->first_name ?? 'there' }}</strong>,</p>
			<p>This email confirms that your ballot was received.</p>
			<p class="meta">
				<strong>Election:</strong> {{ $election->title }}<br>
				<strong>Submitted:</strong> {{ $submittedAtDisplay }}<br>
				@if(!empty($user->member_id))
					<strong>Member ID:</strong> {{ $user->member_id }}
				@endif
			</p>
			<p style="margin-top:24px;font-weight:700;color:#111827;">Your selections</p>
			@foreach($receiptRows as $row)
				<p class="position">{{ $row['position'] }}</p>
				@if(count($row['choices']) === 0)
					<p class="skipped">No selection for this position.</p>
				@else
					<ul class="choices">
						@foreach($row['choices'] as $line)
							<li>{{ $line }}</li>
						@endforeach
					</ul>
				@endif
			@endforeach
			<p style="margin-top:28px;font-size:14px;color:#6b7280;">Keep this message for your records. If you did not cast this vote, contact support immediately.</p>
		</div>
	</div>
	<p class="footer">© {{ date('Y') }} PASEI. All rights reserved.</p>
</body>
</html>
