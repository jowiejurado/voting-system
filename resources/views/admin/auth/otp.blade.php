@php
	$title = 'OTP | Voting System';
	$otpDeliveryChannel = ($otpDeliveryChannel ?? 'sms') === 'email' ? 'email' : 'sms';
	$maskedPhone = $maskedPhone ?? '';
	$maskedEmail = $maskedEmail ?? '';
	$alternateOtpChannel = $otpDeliveryChannel === 'sms' ? 'email' : 'sms';
	$alternateOtpButtonLabel = $otpDeliveryChannel === 'sms' ? 'Send OTP via Email' : 'Send OTP via Phone number';
@endphp
@extends('layouts.auth')
@section('content')
	<div class="flex items-center mx-auto gap-[120px]">
		<img src="{{ asset('logo.png') }}" alt="Logo" width="350" height="350" class="bg-white rounded-full p-0 m-0" />
		<div class="bg-white p-[32px] rounded-4xl max-w-[500px] items-center flex flex-col">
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

			<form method="post" action="{{ $sendOtpRoute ?? route('admin.send-otp') }}" class="mt-4">
				@csrf
				<input type="hidden" name="context" value="login">
				<input type="hidden" name="channel" value="{{ $alternateOtpChannel }}">
				<button class="inline-block py-3 px-6 rounded-3xl border-none bg-gray-100 text-black cursor-pointer font-semibold" type="submit">
					{{ $alternateOtpButtonLabel }}
				</button>
			</form>
		</div>
	</div>
@endsection
