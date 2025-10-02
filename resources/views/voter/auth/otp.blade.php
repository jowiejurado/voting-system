@php($title = 'Voter OTP')
<x-layouts.app :title="$title">
	<div class="card" style="max-width:420px;margin:24px auto;">
		<form method="post" action="{{ route('voter.otp.verify') }}">
			@csrf
			<label class="label">Enter the 6-digit code sent to your phone</label>
			<input class="input" type="text" name="code" maxlength="6" required>
			<div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end;">
				<button class="btn" type="submit">Verify</button>
			</div>
		</form>
	</div>
</x-layouts.app>


