@php($title = 'Votes')
<x-layouts.app :title="$title">
	<div class="card">
		<div class="label">Election: {{ $election?->title ?? 'None' }}</div>
		<table class="table">
			<tr><th>Position</th><th>Total Votes</th></tr>
			@foreach($positions as $p)
				<tr>
					<td>{{ $p->name }}</td>
					<td>{{ $tally[$p->name] ?? 0 }}</td>
				</tr>
			@endforeach
		</table>
	</div>
</x-layouts.app>


