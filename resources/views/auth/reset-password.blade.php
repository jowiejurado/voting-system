@php($title = ($heading ?? 'Reset Password') . ' | Voting System')
@extends('layouts.auth')

@section('content')
<div class="max-w-[520px] w-full mx-auto bg-white shadow-2xl p-[32px] rounded-4xl">
	<h4 class="text-lg text-black font-bold mb-4">{{ $heading ?? 'Reset Password' }}</h4>
	<form method="post" action="{{ route($postRoute) }}" class="flex flex-col gap-[16px]">
		@csrf
		<input type="hidden" name="token" value="{{ $token }}">
		<input type="email" name="email" required placeholder="EMAIL ADDRESS"
			class="w-full py-[14px] px-[20px] rounded-3xl bg-gray-100 text-black outline-none border-none"
			value="{{ old('email', $email) }}">
		<input type="password" name="password" required placeholder="NEW PASSWORD"
			class="w-full py-[14px] px-[20px] rounded-3xl bg-gray-100 text-black outline-none border-none">
		<input type="password" name="password_confirmation" required placeholder="CONFIRM NEW PASSWORD"
			class="w-full py-[14px] px-[20px] rounded-3xl bg-gray-100 text-black outline-none border-none">

		<button class="inline-block py-4 px-8 rounded-3xl border-none bg-black text-white cursor-pointer font-semibold" type="submit">
			Update password
		</button>
	</form>

	@if ($errors->any())
		<ul class="mt-4 text-red-600">
			@foreach ($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
		</ul>
	@endif
</div>
@endsection


