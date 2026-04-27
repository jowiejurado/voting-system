@php
	$title = 'OTP | Voting System';
	$otpDeliveryChannel = ($otpDeliveryChannel ?? 'sms') === 'email' ? 'email' : 'sms';
	$maskedPhone = $maskedPhone ?? '';
	$maskedEmail = $maskedEmail ?? '';
	$alternateOtpChannel = $otpDeliveryChannel === 'sms' ? 'email' : 'sms';
	$alternateOtpButtonLabel = $otpDeliveryChannel === 'sms' ? 'Send OTP via Email' : 'Send OTP via Phone number';
	$resendOtpLabel = $otpDeliveryChannel === 'email' ? 'Resend code to email' : 'Resend code via SMS';
@endphp
@extends('layouts.auth')
@section('content')
	<div class="flex flex-col md:flex-row items-center justify-center mx-auto gap-8 md:gap-16 lg:gap-[120px] w-full max-w-5xl px-2">
		<img src="{{ asset('logo.png') }}" alt="Logo" width="350" height="350" class="bg-white rounded-full p-0 m-0 w-40 h-40 sm:w-52 sm:h-52 md:w-64 md:h-64 lg:w-[350px] lg:h-[350px] object-contain shrink-0" />
		<div class="bg-white p-6 sm:p-[32px] rounded-4xl max-w-[500px] w-full items-center flex flex-col min-w-0">
			<form method="post" action="{{ $otpVerifyRoute ?? route('admin.otp.verify') }}" class="flex flex-col items-center gap-[24px]">
				@csrf
				<h4 class="text-lg text-black font-bold">2nd Step AUTHENTICATION - OTP Verification</h4>
				<label class="label">
					@if ($otpDeliveryChannel === 'email')
						Check your OTP on this email {{ $maskedEmail }}
					@else
						Check your OTP on this number {{ $maskedPhone }}
					@endif
				</label>
				<input type="text" id="code" name="code" required placeholder="CODE" maxlength="6" class="w-100 py-[16px] px-[24px] rounded-3xl bg-gray-100 text-black outline-none border-none">
				<button class="inline-block py-4 px-8 rounded-3xl border-none bg-black text-white cursor-pointer font-semibold" type="submit">Proceed</button>
			</form>

			<div class="mt-6 w-full max-w-sm flex flex-col items-center gap-3 border-t border-gray-200 pt-6">
				<p class="text-sm text-gray-600 text-center m-0">Didn't receive the code?</p>
				<form method="post" action="{{ $sendOtpRoute ?? route('admin.send-otp') }}" class="w-full flex justify-center">
					@csrf
					<input type="hidden" name="context" value="login">
					<input type="hidden" name="channel" value="{{ $otpDeliveryChannel }}">
					<button type="submit" data-otp-send class="inline-block py-3 px-6 rounded-3xl border-2 border-gray-900 bg-white text-black cursor-pointer font-semibold w-full sm:w-auto text-center">
						{{ $resendOtpLabel }}
					</button>
				</form>
				<p class="text-xs text-gray-500 text-center m-0 px-2">Only the most recent code is valid.</p>
			</div>

			<form method="post" action="{{ $sendOtpRoute ?? route('admin.send-otp') }}" class="mt-4 flex flex-col items-center gap-2">
				@csrf
				<input type="hidden" name="context" value="login">
				<input type="hidden" name="channel" value="{{ $alternateOtpChannel }}">
				<span class="text-xs text-gray-500 uppercase tracking-wide">Or use another method</span>
				<button type="submit" data-otp-send class="inline-block py-3 px-6 rounded-3xl border-none bg-gray-100 text-black cursor-pointer font-semibold">
					{{ $alternateOtpButtonLabel }}
				</button>
			</form>
		</div>
	</div>
@endsection

@push('scripts')
	<script>
		(function () {
			var COOLDOWN_MS = 45000;
			var forms = document.querySelectorAll('form[action*="send-otp"]');
			if (!forms.length) return;

			function parseCooldownEnd() {
				var raw = sessionStorage.getItem('otpSendCooldownUntil');
				var n = raw ? parseInt(raw, 10) : 0;
				return isNaN(n) ? 0 : n;
			}

			function setButtonsDisabled(disabled, label) {
				document.querySelectorAll('[data-otp-send]').forEach(function (btn) {
					btn.disabled = disabled;
					if (label) btn.textContent = label;
				});
			}

			function restoreButtonLabels() {
				document.querySelectorAll('[data-otp-send]').forEach(function (btn) {
					btn.textContent = btn.getAttribute('data-label') || btn.textContent;
				});
			}

			document.querySelectorAll('[data-otp-send]').forEach(function (btn) {
				if (!btn.getAttribute('data-label')) btn.setAttribute('data-label', btn.textContent.trim());
			});

			function tick() {
				var until = parseCooldownEnd();
				var left = until - Date.now();
				if (left <= 0) {
					setButtonsDisabled(false);
					restoreButtonLabels();
					sessionStorage.removeItem('otpSendCooldownUntil');
					return;
				}
				setButtonsDisabled(true, 'Wait ' + Math.ceil(left / 1000) + 's');
				setTimeout(tick, 500);
			}

			var until = parseCooldownEnd();
			if (until > Date.now()) tick();

			forms.forEach(function (form) {
				form.addEventListener('submit', function () {
					sessionStorage.setItem('otpSendCooldownUntil', String(Date.now() + COOLDOWN_MS));
				});
			});
		})();
	</script>
@endpush
