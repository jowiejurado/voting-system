@php
	$title = 'Voting System | Admin - OTP';
	$masked = '+639*****530'
	// $phone  = (string) Auth::user()->phone_number;
	// $maskLen = max(strlen($phone) - 7, 0);
	// $masked = \Illuminate\Support\Str::mask($phone, '*', 4, $maskLen);
@endphp
@extends('layouts.auth')
@section('content')
	<div class="flex items-center mx-auto gap-[120px]">
		<img src="{{ asset('logo.png') }}" alt="Logo" width="350" height="350" class="bg-white rounded-full p-0 m-0" />
		<div class="bg-white p-[32px] rounded-4xl max-w-[500px]">
			<form method="post" action="{{ route('admin.otp.verify') }}" class="flex flex-col items-center gap-[24px]">
				@csrf
				<h4 class="text-lg text-black font-bold">2nd Step AUTHENTICATION - Phone OTP Verification</h4>
				<label class="label">Check your OTP on this number {{ $masked }}</label>
				<input type="text" id="code" name="code" required placeholder="CODE" maxlength="6" class="w-100 py-[16px] px-[24px] rounded-3xl bg-gray-100 text-black outline-none border-none">
				<button class="inline-block py-4 px-8 rounded-3xl border-none bg-black text-white cursor-pointer font-semibold" type="submit">Proceed</button>
			</form>
		</div>
	</div>
@endsection
