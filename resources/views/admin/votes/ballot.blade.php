@php($title = 'Ballot Preview')
<x-layouts.app :title="$title">
	<div class="card">
		<div class="label">Election: {{ $election?->title ?? 'None' }}</div>
		@foreach($positions as $p)
			<div class="card">
				<div class="badge">{{ $p->name }}</div>
				<div class="row">
					@foreach($p->candidates as $c)
						<div class="col">
							<div class="card">
								<strong>{{ $c->name }}</strong>
								<div class="label">{{ $c->party ?? 'Independent' }}</div>
							</div>
						</div>
					@endforeach
				</div>
			</div>
		@endforeach
	</div>
</x-layouts.app>


