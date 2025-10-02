@php($title = 'Edit Candidate')
<x-layouts.app :title="$title">
	<div class="card" style="max-width:520px;margin:24px auto;">
		<form method="post" action="{{ route('admin.candidates.update', $candidate) }}">
			@csrf
			@method('PUT')
			<label class="label">Election</label>
			<select class="input" name="election_id">
				@foreach($elections as $e)
					<option value="{{ $e->id }}" @selected($e->id==$candidate->election_id)>{{ $e->title }}</option>
				@endforeach
			</select>
			<label class="label">Position</label>
			<select class="input" name="position_id">
				@foreach($positions as $p)
					<option value="{{ $p->id }}" @selected($p->id==$candidate->position_id)>{{ $p->name }}</option>
				@endforeach
			</select>
			<label class="label">Name</label>
			<input class="input" type="text" name="name" value="{{ $candidate->name }}" required>
			<label class="label">Party</label>
			<input class="input" type="text" name="party" value="{{ $candidate->party }}">
			<div style="margin-top:12px;display:flex;justify-content:flex-end;gap:8px">
				<a class="btn secondary" href="{{ route('admin.candidates.index') }}">Cancel</a>
				<button class="btn" type="submit">Save</button>
			</div>
		</form>
	</div>
</x-layouts.app>


