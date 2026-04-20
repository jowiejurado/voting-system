@php
	$title = 'Voter - OTP | Voting System';
	$masked = '';
	$maskedEmail = '';

	if (!Auth::user()) {
		redirect()->route('auth.login');
	} else {
		$phone  = (string) Auth::user()->phone_number;
		$maskLen = max(strlen($phone) - 7, 0);
		$masked = \Illuminate\Support\Str::mask($phone, '*', 4, $maskLen);
	}
@endphp
@extends('layouts.voter-auth')
@section('content')
	<div class="flex flex-col md:flex-row items-center justify-center mx-auto gap-8 md:gap-16 lg:gap-[120px] w-full max-w-5xl px-2">
		<img src="{{ asset('logo.png') }}" alt="Logo" width="350" height="350" class="bg-white rounded-full p-0 m-0 w-40 h-40 sm:w-52 sm:h-52 md:w-64 md:h-64 lg:w-[350px] lg:h-[350px] object-contain shrink-0" />
		<div class="flex flex-col justify-center items-center w-full min-w-0">
			<h1 class="text-base sm:text-xl md:text-2xl uppercase text-black font-black text-center mb-4 sm:mb-8 px-1 leading-tight">PASEI SECURED ONLINE VOTING SYSTEM</h1>
			<div class="bg-white shadow-2xl p-6 sm:p-[32px] rounded-4xl max-w-[500px] w-full items-center flex flex-col">
				<form method="post" action="{{ route('voter.otp.verify') }}" class="flex flex-col items-center gap-[24px]">
					@csrf
					<h4 class="text-lg text-black font-bold">2nd Step Verification - OTP Verification</h4>
					<label class="label">Check your OTP on this number {{ $masked ?? '' }}</label>
					<input type="text" id="code" name="code" required placeholder="CODE" maxlength="6" class="w-100 py-[16px] px-[24px] rounded-3xl bg-gray-100 text-black outline-none border-none">
					<button class="inline-block py-4 px-8 rounded-3xl border-none bg-black text-white cursor-pointer font-semibold" type="submit">Proceed</button>
				</form>

				<div class="flex flex-col items-center gap-2 mt-4">
					<form method="post" action="{{ route('voter.send-otp') }}">
						@csrf
						<input type="hidden" name="context" value="login">
						<input type="hidden" name="channel" value="email">
						<button class="inline-block py-3 px-6 rounded-3xl border-none bg-gray-100 text-black cursor-pointer font-semibold" type="submit">
							Send OTP via Email
						</button>
					</form>
				</div>

				{{-- <a href="{{ route('admin.otp.resend') }}" class="inline-block py-4 px-8 border-none bg-transparent text-black cursor-pointer font-semibold">Send another OTP</a> --}}
			</div>
		</div>
	</div>
@endsection
