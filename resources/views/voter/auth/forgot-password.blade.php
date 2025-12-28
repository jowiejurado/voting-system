@php($title = 'Voter - Forgot Password | Voting System')
@extends('layouts.voter-auth')

@section('content')
<div class="panel flex flex-col justify-center items-center mx-auto">
	<h1 class="text-2xl uppercase text-black font-black text-center mb-8">PASEI SECURED ONLINE VOTING SYSTEM</h1>
	<div class="bg-white shadow-2xl p-[32px] rounded-4xl max-w-[500px] w-full md:w-auto">
		<h4 class="text-lg text-black font-bold mb-4">Forgot your password?</h4>
		<p class="text-gray-600 mb-6">Enter the email address associated with your voter account and we’ll send a reset link.</p>
		<form method="post" action="{{ route('voter.password.email') }}" class="flex flex-col gap-[16px]">
			@csrf
			<input type="email" name="email" required placeholder="EMAIL ADDRESS"
							 class="w-full py-[14px] px-[20px] rounded-3xl bg-gray-100 text-black outline-none border-none" value="{{ old('email') }}">
			<button class="inline-block py-4 px-8 rounded-3xl border-none bg-black text-white cursor-pointer font-semibold" type="submit">
				Send reset link
			</button>
		</form>
		@if (session('success'))
			<p class="text-green-600 mt-4">{{ session('success') }}</p>
		@endif
		@if (session('error'))
			<p class="text-red-600 mt-4">{{ session('error') }}</p>
		@endif
	</div>
</div>
@endsection


