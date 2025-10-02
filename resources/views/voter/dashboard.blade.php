@php($title = 'Voter Dashboard')
<x-layouts.app :title="$title">
	<x-slot:nav>
		<form method="post" action="{{ route('voter.logout') }}" style="display:inline">
			@csrf
			<button class="btn secondary" type="submit">Logout</button>
		</form>
	</x-slot:nav>
	<div class="card">
		<h2 style="margin:0 0 8px 0">Welcome, {{ auth()->user()->name }}</h2>
		<p class="label">Active election: {{ optional($active ?? null)->title ?? 'None' }}</p>
		<div style="display:flex;gap:8px;flex-wrap:wrap">
			<a class="btn" href="{{ route('voter.ballot') }}">Go to Ballot</a>
			<a class="btn secondary" href="{{ route('voter.account') }}">Account Settings</a>
		</div>
	</div>
</x-layouts.app>


