@php($title = 'Add Position')
<x-layouts.app :title="$title">
	<div class="card" style="max-width:520px;margin:24px auto;">
		<form method="post" action="{{ route('admin.positions.store') }}">
			@csrf
			<label class="label">Election</label>
			<select class="input" name="election_id">
				@foreach($elections as $e)
					<option value="{{ $e->id }}">{{ $e->title }}</option>
				@endforeach
			</select>
			<label class="label">Name</label>
			<input class="input" type="text" name="name" required>
			<label class="label">Max Votes</label>
			<input class="input" type="number" name="max_votes" value="1" min="1" required>
			<label class="label">Order</label>
			<input class="input" type="number" name="order_index" value="0" min="0">
			<div style="margin-top:12px;display:flex;justify-content:flex-end;gap:8px">
				<a class="btn secondary" href="{{ route('admin.positions.index') }}">Cancel</a>
				<button class="btn" type="submit">Save</button>
			</div>
		</form>
	</div>
</x-layouts.app>


