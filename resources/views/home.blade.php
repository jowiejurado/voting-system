@php($title = 'Welcome')
@extends('layouts.auth')

@section('content')
	<div class="card">
		<h2 style="margin:0 0 8px 0">Welcome</h2>
		<p class="label">Choose a panel</p>
		<div style="display:flex;gap:8px;flex-wrap:wrap">
			<a class="btn" href="{{ route('admin.login') }}">Admin Login</a>
			<a class="btn secondary" href="{{ route('voter.login') }}">Voter Login</a>
		</div>
	</div>
@endsection


