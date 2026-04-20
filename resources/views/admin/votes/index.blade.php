@php($title = 'Votes | Voting System')

@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 px-3 sm:px-6 lg:px-10 pt-5">
  <div class="flex flex-col gap-y-5">
    <h1 class="text-2xl font-black text-[#0b252a]">Votes</h1>
    @if ($activeElection ?? null)
    <div class="flex items-stretch sm:items-center justify-end">
			<form id="search-form" method="GET" action="{{ route('admin.votes.index') }}"
						class="flex flex-wrap items-center gap-x-2 gap-y-2 w-full sm:w-auto justify-end">
				<label for="search">Search:</label>
				<input id="search" name="q" type="search"
							value="{{ $q ?? '' }}"
							placeholder="Type keywords..."
							class="border-2 border-gray-300 py-1 px-2 outline-none"
							autofocus />
			</form>
		</div>
    @else
    <p class="text-sm text-gray-700 max-w-2xl">
      The vote list is only available while an election is open for voting. After voting closes, this list is cleared until the next active election.
    </p>
    @endif
  </div>

	<div id="table-wrap" class="relative border-2 border-gray-400 rounded-3xl w-full overflow-x-auto">
		<table class="table-fixed w-full min-w-[640px] text-sm sm:text-base" id="votes-table">
      <thead>
        <tr class="border-b-2 border-gray-400">
          <th class="py-3 px-6 text-center w-[5%]">No.</th>
					<th class="py-3 px-6 text-center">Position</th>
					<th class="py-3 px-6 text-center">Candidate</th>
					<th class="py-3 px-6 text-center">Organization</th>
        </tr>
      </thead>
      <tbody>
        @forelse($votes as $index => $vote)
          <tr class="border-b-2 border-gray-400 last:border-b-0">
            <td class="w-[5%] py-3 px-6 text-center">{{ $index + 1 }}</td>
						<td class="py-3 px-6 text-center w-[15%]">
							{{ $vote->position['name'] }}
						</td>
						<td class="py-3 px-6 text-center">
							{{ $vote->candidate['first_name'] }} {{ $vote->candidate['last_name'] }}
						</td>
						<td class="py-3 px-6 text-center">
							{{ $vote->candidate?->organization?->name }}
						</td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="py-6 text-center text-gray-500">No votes yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
		<div id="table-loading"
			class="hidden absolute inset-0 bg-white/60 backdrop-blur-[2px] flex items-center justify-center">
			<div class="w-10 h-10 border-4 border-gray-300 border-t-black rounded-full animate-spin"></div>
		</div>
  </div>

	@if ($activeElection ?? null)
	<div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center justify-end gap-3 sm:gap-x-5 px-4 py-3">
		<form id="per-page-form" method="GET" action="{{ route('admin.votes.index') }}"
					class="flex gap-x-2 items-center">
			<label class="text-sm text-gray-600">Items per page:</label>
			<input type="hidden" name="q" value="{{ $q }}">
			<input type="hidden" name="page" value="1"> {{-- reset to first page --}}
			<select name="per_page" class="border-2 border-gray-300 py-1 px-2"
							onchange="this.form.submit()">
				@foreach([5,10,15,25,50] as $n)
					<option value="{{ $n }}" @selected(($perPage ?? 10) == $n)>{{ $n }}</option>
				@endforeach
			</select>
		</form>

		<div class="text-sm text-gray-600">
			Showing {{ $votes->firstItem() ?? 0 }} – {{ $votes->lastItem() ?? 0 }} of {{ $votes->total() }}
		</div>

		<div id="pagination">
			{{ $votes->onEachSide(1)->links('vendor.pagination.always') }}
		</div>
	</div>
	@endif
</div>
@endsection

@push('scripts')
<script>
	function showTableLoading() {
    const el = document.getElementById('table-loading');
    if (el) el.classList.remove('hidden');
  }
	// Hide loader if user navigates back/forward via bfcache
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) {
      const el = document.getElementById('table-loading');
      if (el) el.classList.add('hidden');
    }
  });

 (function(){
    const input = document.getElementById('search');
    const form  = document.getElementById('search-form');
    if (!input || !form) return;
    let t;
    input.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => {
        showTableLoading();
        form.submit();
      }, 350);
    });

    // Keep focus + caret on load
    input.focus();
    const len = input.value.length;
    try { input.setSelectionRange(len, len); } catch(e){}
  })();

	(function(){
    const perPageForm = document.getElementById('per-page-form');
    if (!perPageForm) return;
    const select = perPageForm.querySelector('select[name="per_page"]');
    if (!select) return;
    select.addEventListener('change', () => {
      showTableLoading();
      // submit handled by inline onchange
    });
  })();

  // --- Pagination links: show loader before navigation ---
  (function(){
    const pager = document.getElementById('pagination');
    if (!pager) return;
    pager.addEventListener('click', (e) => {
      const a = e.target.closest('a');
      if (!a) return;          // clicking on current page (span) does nothing
      showTableLoading();
      // let the browser navigate normally
    });
  })();
</script>
@endpush

