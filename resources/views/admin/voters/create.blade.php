@php($title = 'Add Voter')
<x-layouts.app :title="$title">
	<div class="card" style="max-width:520px;margin:24px auto;">
		<form method="post" action="{{ route('admin.voters.store') }}">
			@csrf
			<label class="label">Name</label>
			<input class="input" type="text" name="name" required>
			<label class="label">Member ID</label>
			<input class="input" type="text" name="member_id" required>
			<label class="label">Phone</label>
			<input class="input" type="text" name="phone">
			<label class="label">Password</label>
			<input class="input" type="password" name="password" required>
			<div style="margin-top:12px;display:flex;justify-content:flex-end;gap:8px">
				<a class="btn secondary" href="{{ route('admin.voters.index') }}">Cancel</a>
				<button class="btn" type="submit">Save</button>
			</div>
		</form>
	</div>
</x-layouts.app>


