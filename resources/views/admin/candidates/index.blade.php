@php($title = 'Candidates')
<x-layouts.app :title="$title">
	<x-slot:nav>
		<a class="btn secondary" href="{{ route('admin.candidates.create') }}">Add Candidate</a>
	</x-slot:nav>
	<div class="card">
		<div class="label">Election: {{ $election?->title ?? 'None' }}</div>
		<table class="table">
			<tr><th>#</th><th>Name</th><th>Party</th><th>Position</th><th>Actions</th></tr>
			@foreach($candidates as $c)
				<tr>
					<td>{{ $c->id }}</td>
					<td>{{ $c->name }}</td>
					<td>{{ $c->party ?? 'Independent' }}</td>
					<td>{{ $c->position?->name }}</td>
					<td>
						<a href="{{ route('admin.candidates.edit', $c) }}">Edit</a>
						<form method="post" action="{{ route('admin.candidates.destroy', $c) }}" style="display:inline">
							@csrf @method('DELETE')
							<button class="btn danger" onclick="return confirm('Delete?')">Delete</button>
						</form>
					</td>
				</tr>
			@endforeach
		</table>
	</div>
</x-layouts.app>


