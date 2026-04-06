@php($title = 'Forgot Password | Voting System')
@extends('layouts.auth')

@section('content')
<div class="max-w-[520px] w-full mx-auto bg-white shadow-2xl p-[32px] rounded-4xl">
	<h4 class="text-lg text-black font-bold mb-4">Forgot your password?</h4>
	<p class="text-gray-600 mb-6">Enter the email address on your account and we’ll send a reset link if it matches.</p>
	<form method="post" action="{{ route('auth.password.email') }}" class="flex flex-col gap-[16px]">
		@csrf
		<input type="email" name="email" required placeholder="EMAIL ADDRESS"
			class="w-full py-[14px] px-[20px] rounded-3xl bg-gray-100 text-black outline-none border-none" value="{{ old('email') }}">
		<button class="inline-block py-4 px-8 rounded-3xl border-none bg-black text-white cursor-pointer font-semibold" type="submit">
			Send reset link
		</button>
	</form>
	<a href="{{ route('auth.login') }}" class="mt-6 inline-block text-sm text-blue-600 font-medium">Back to sign in</a>
</div>
@endsection
