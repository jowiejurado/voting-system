@php($title = 'Face Verification')
<x-layouts.app :title="$title">
	<div class="card" style="max-width:520px;margin:24px auto;">
		<form method="post" action="{{ route('voter.face.verify') }}" enctype="multipart/form-data">
			@csrf
			<label class="label">Upload a clear face photo</label>
			<input class="input" type="file" name="photo" accept="image/*" capture="user" required>
			<div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end;">
				<button class="btn" type="submit">Verify Face</button>
			</div>
		</form>
	</div>
</x-layouts.app>


