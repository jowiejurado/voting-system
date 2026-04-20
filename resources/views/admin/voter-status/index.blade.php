@php($title = 'Voter Status | Voting System')

@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 px-3 sm:px-6 lg:px-10 pt-5">
  <div class="flex flex-col gap-y-5">
    <h1 class="text-2xl font-black text-[#0b252a]">Voter Status</h1>
    <div class="flex flex-col items-stretch sm:items-end gap-3">
			<form
				id="search-form"
				method="GET"
				action="{{ route('admin.voter-status.index') }}"
				class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-2 sm:gap-x-2 w-full sm:w-auto"
			>
				<label for="search">Search:</label>
				<input
					id="search"
					name="q"
					type="search"
					value="{{ $q ?? '' }}"
					placeholder="Type keywords..."
					class="border-2 border-gray-300 py-1 px-2 outline-none"
					autofocus
					autocomplete="off"
				/>

				<label for="filter" class="sm:ml-4">Filter:</label>
				<select
					id="filter"
					name="filter"
					class="border-2 border-gray-300 py-1 px-2 outline-none"
					onchange="document.getElementById('search-form').submit()"
				>
					<option value="" {{ empty($filter ?? '') ? 'selected' : '' }}>
						All
					</option>
					<option value="voted" {{ ($filter ?? '') === 'voted' ? 'selected' : '' }}>
						Voted
					</option>
					<option value="undone" {{ ($filter ?? '') === 'undone' ? 'selected' : '' }}>
						Undone
					</option>
				</select>

				{{-- Keep per_page if you’re using it elsewhere --}}
				<input type="hidden" name="per_page" value="{{ $perPage ?? 9 }}">
			</form>
		</div>
  </div>

	<div class="flex flex-wrap justify-stretch sm:justify-evenly gap-x-4 sm:gap-x-8 gap-y-6 text-white">
		@forelse($voters as $voter)
			<div class="flex justify-between w-full sm:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.75rem)] max-w-full rounded-4xl px-4 py-5 relative min-w-0 {{ $voter->has_voted ? 'bg-green-700' : 'bg-[#545454]' }}">
				<div class="flex flex-col justify-center gap-y-4 min-w-0 flex-1 pr-2">
					<span class="text-lg sm:text-2xl capitalize break-words">{{ $voter->first_name }} {{ $voter->last_name }}</span>

					@if ($voter->organization_name)
						<span class="px-2.5 py-2 bg-[#243539] rounded-4xl text-xs text-center w-fit max-w-full">{{ $voter->organization_name }}</span>
					@endif
				</div>

				<div class="h-full w-15 bg-transparent flex items-center">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-full w-15 text-white" viewBox="0 0 24 24" fill="currentColor">
						<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/>
					</svg>
				</div>

				@if (!$voter->has_voted)
					<div class="absolute bottom-0 -right-5 bg-[#a6a6a6] font-semibold rounded-full px-3 py-2 -rotate-[35deg]">
						Undone
					</div>
				@else
					<div class="absolute bottom-0 -right-5 bg-[#5fb643] font-semibold rounded-full px-3 py-2 -rotate-[35deg]">
						Voted
					</div>
				@endif
			</div>
		@empty
			No voters registered yet
		@endforelse
	</div>

	@if ($voters->hasPages())
		<div class="flex items-center justify-center gap-4 mt-8">
			@if ($voters->previousPageUrl())
				<a
					href="{{ $voters->previousPageUrl() }}"
					class="flex gap-x-2 items-center justify-center px-4 py-2 rounded-full bg-gray-200 text-gray-800 hover:bg-gray-300 transition"
				>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
					</svg>
					Previous
				</a>
			@else
				<span class="flex gap-x-2 items-center justify-center px-4 py-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
					</svg>
					Previous
				</span>
			@endif

			@if ($voters->nextPageUrl())
				<a
					href="{{ $voters->nextPageUrl() }}"
					class="flex gap-x-2 items-center justify-center px-4 py-2 rounded-full bg-gray-200 text-gray-800 hover:bg-gray-300 transition"
				>
					Next
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5">
						<path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
					</svg>
				</a>
			@else
				<span class="flex gap-x-2 items-center justify-center px-4 py-2 rounded-full bg-gray-100 text-gray-400 cursor-not-allowed">
					Next
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5">
						<path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
					</svg>
				</span>
			@endif
		</div>
	@endif
</div>
@endsection

@push('scripts')
<script>
(function () {
	const form = document.getElementById('search-form');
	const search = document.getElementById('search');
	if (!form || !search) return;

	let debounceTimer;
	const delayMs = 250;

	function submitSearch() {
		clearTimeout(debounceTimer);
		debounceTimer = setTimeout(function () {
			if (typeof form.requestSubmit === 'function') {
				form.requestSubmit();
			} else {
				form.submit();
			}
		}, delayMs);
	}

	search.addEventListener('input', submitSearch);
	search.addEventListener('search', submitSearch);
})();
</script>
@endpush
