@php($title = 'Voters')
<x-layouts.app :title="$title">
	<x-slot:nav>
		<a class="btn secondary" href="{{ route('admin.voters.create') }}">Add Voter</a>
	</x-slot:nav>
	<div class="card">
		<form method="get" style="display:flex;gap:8px">
			<input class="input" type="text" name="q" value="{{ $q }}" placeholder="Search name/member/phone">
			<button class="btn" type="submit">Search</button>
		</form>
	</div>
	<div class="card">
		<table class="table">
			<tr><th>ID</th><th>Name</th><th>Member ID</th><th>Phone</th><th>Actions</th></tr>
			@foreach($voters as $v)
				<tr>
					<td>{{ $v->id }}</td>
					<td>{{ $v->name }}</td>
					<td>{{ $v->member_id }}</td>
					<td>{{ $v->phone }}</td>
					<td>
						<a href="{{ route('admin.voters.edit', $v) }}">Edit</a>
						<form method="post" action="{{ route('admin.voters.destroy', $v) }}" style="display:inline">
							@csrf @method('DELETE')
							<button class="btn danger" onclick="return confirm('Delete?')">Delete</button>
						</form>
					</td>
				</tr>
			@endforeach
		</table>
		<div style="margin-top:8px">{{ $voters->links() }}</div>
	</div>
</x-layouts.app>


