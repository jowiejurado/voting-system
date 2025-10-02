@php($title = 'Ballot')
<x-layouts.app :title="$title">
	@if(!$position)
		<div class="card" style="max-width:720px;margin:24px auto;">
			<h3>Ballot complete</h3>
			<form method="post" action="{{ route('voter.ballot.submit') }}">
				@csrf
				<button class="btn" type="submit">Review and Submit</button>
			</form>
		</div>
	@else
		<div class="card" style="max-width:720px;margin:24px auto;">
			<div class="badge">Step {{ $currentIndex+1 }} of {{ count($positions) }}</div>
			<h3 style="margin:8px 0">{{ $position->name }}</h3>
			<form method="post" action="{{ route('voter.ballot.step') }}">
				@csrf
				<input type="hidden" name="position_id" value="{{ $position->id }}">
				<input type="hidden" name="current_index" value="{{ $currentIndex }}">
				<div class="row">
					@foreach($position->candidates as $c)
						<div class="col">
							<div class="card">
								<div style="font-weight:700">{{ $c->name }}</div>
								<div class="label">{{ $c->party ?? 'Independent' }}</div>
								<button class="btn" name="candidate_id" value="{{ $c->id }}">Vote</button>
							</div>
						</div>
					@endforeach
				</div>
				<div style="margin-top:12px;text-align:right">
					<button class="btn secondary" name="candidate_id" value="">Skip</button>
				</div>
			</form>
		</div>
	@endif
</x-layouts.app>


