@php($title = 'Voter Login')
<x-layouts.app :title="$title">
	<x-slot:nav>
		<a href="{{ route('voter.login') }}">Voter</a>
	</x-slot:nav>
	<div class="card" style="max-width:420px;margin:24px auto;">
		<form method="post" action="{{ route('voter.login.submit') }}">
			@csrf
			<label class="label">Member ID</label>
			<input class="input" type="text" name="member_id" required>
			<label class="label">Password</label>
			<input class="input" type="password" name="password" required>
			<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
			<div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end;">
				<button class="btn" type="submit">Login</button>
			</div>
		</form>
	</div>
	@push('scripts')
	<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
	<script>
	grecaptcha.ready(function(){
		grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'voter_login'}).then(function(token){
			document.getElementById('g-recaptcha-response').value = token;
		});
	});
	</script>
	@endpush
</x-layouts.app>


