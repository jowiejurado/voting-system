@php($title = 'Vote Summary')
<x-layouts.app :title="$title">
	<div class="card" style="max-width:720px;margin:24px auto;">
		<h3 style="margin:8px 0">Summary</h3>
		<table class="table">
			<tr><th>Position</th><th>Choice</th></tr>
			@foreach($positions as $p)
				@php($vote = $votes->firstWhere('position_id', $p->id))
				<tr>
					<td>{{ $p->name }}</td>
					<td>{{ optional(optional($vote)->candidate)->name ?? 'Skipped' }}</td>
				</tr>
			@endforeach
		</table>
		<div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end">
			<a class="btn secondary" href="{{ route('voter.ballot', ['step' => 0]) }}">Reject</a>
			<a class="btn" href="{{ route('voter.dashboard') }}">Submit</a>
		</div>
	</div>
</x-layouts.app>


