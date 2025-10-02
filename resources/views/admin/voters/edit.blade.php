@php($title = 'Edit Voter')
<x-layouts.app :title="$title">
	<div class="card" style="max-width:520px;margin:24px auto;">
		<form method="post" action="{{ route('admin.voters.update', $voter) }}">
			@csrf
			@method('PUT')
			<label class="label">Name</label>
			<input class="input" type="text" name="name" value="{{ $voter->name }}" required>
			<label class="label">Member ID</label>
			<input class="input" type="text" name="member_id" value="{{ $voter->member_id }}" required>
			<label class="label">Phone</label>
			<input class="input" type="text" name="phone" value="{{ $voter->phone }}">
			<label class="label">New Password (optional)</label>
			<input class="input" type="password" name="password">
			<div style="margin-top:12px;display:flex;justify-content:flex-end;gap:8px">
				<a class="btn secondary" href="{{ route('admin.voters.index') }}">Cancel</a>
				<button class="btn" type="submit">Save</button>
			</div>
		</form>
	</div>
</x-layouts.app>


