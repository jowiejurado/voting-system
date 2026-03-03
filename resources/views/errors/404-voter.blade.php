@php($title = 'Page Not Found | Voter Panel')
@extends('layouts.voter-auth')

@section('content')
<div class="w-full mx-auto max-w-xl px-6 text-center">
	<div class="rounded-3xl bg-white/95 backdrop-blur-sm shadow-2xl ring-1 ring-black/5 p-10">
		<div class="text-8xl font-bold text-slate-200">404</div>
		<h1 class="mt-4 text-xl font-semibold text-slate-800">Page not found</h1>
		<p class="mt-2 text-slate-600">This page does not exist on the Voter Panel.</p>
		<a href="{{ route('voter.login') }}" class="mt-8 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-white font-medium hover:bg-emerald-700 transition">
			<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
			</svg>
			Back to Voter Login
		</a>
	</div>
</div>
@endsection
