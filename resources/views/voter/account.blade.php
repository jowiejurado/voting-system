@php($title = 'Account Settings')
<x-layouts.app :title="$title">
	<div class="card" style="max-width:520px;margin:24px auto;">
		<form method="post" action="{{ route('voter.account.otp') }}" style="margin-bottom:12px;display:flex;justify-content:flex-end">
			@csrf
			<button class="btn secondary" type="submit">Send code</button>
		</form>
		<form method="post" action="{{ route('voter.account.password') }}">
			@csrf
			<label class="label">Member ID</label>
			<input class="input" type="text" value="{{ auth()->user()->member_id }}" readonly>
			<label class="label">Current Password</label>
			<input class="input" type="password" name="current_password" required>
			<label class="label">New Password</label>
			<input class="input" type="password" name="new_password" required>
			<label class="label">Code</label>
			<input class="input" type="text" name="code" required>
			<div style="margin-top:12px;display:flex;justify-content:flex-end">
				<button class="btn" type="submit">Update</button>
			</div>
		</form>
	</div>
</x-layouts.app>


