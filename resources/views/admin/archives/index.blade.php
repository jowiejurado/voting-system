@php($title = 'Archives Election | Voting System')

@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 px-10 pt-5">
  <div class="flex flex-col gap-y-5">
    <h1 class="text-2xl font-black text-[#0b252a]">Archives Election</h1>
    <div class="flex items-center justify-end">
			<form id="search-form" method="GET" action="{{ route('admin.archives.index') }}"
						class="flex items-center gap-x-2">
				<label for="search">Search:</label>
				<input id="search" name="q" type="search"
							value="{{ $q ?? '' }}"
							placeholder="Type keywords..."
							class="border-2 border-gray-300 py-1 px-2 outline-none"
							autofocus />
			</form>
		</div>
  </div>

	<div id="table-wrap" class="relative border-2 border-gray-400 rounded-3xl w-full overflow-hidden">
		<table class="table-fixed w-full" id="positions-table">
      <thead>
        <tr class="border-b-2 border-gray-400">
          <th class="py-3 px-6 text-center">Election Title</th>
					<th class="py-3 px-6 text-center w-[15%]">Date</th>
					<th class="py-3 px-6 text-center w-[10%]">Time</th>
					<th class="py-3 px-6 text-center w-[10%]">Time Ended</th>
        </tr>
      </thead>
      <tbody>
        @forelse($elections as $election)
          <tr class="border-b-2 border-gray-400 last:border-b-0">
            <td class="py-3 px-6 text-center">{{ $election->title }}</td>
						<td class="py-3 px-6 text-center w-[15%]">
							{{ \Carbon\Carbon::parse($election->date)->format('F d, Y') }}
						</td>
						<td class="py-3 px-6 text-center w-[10%]">
							{{ \Carbon\Carbon::parse($election->start_time)->format('Hi') }}H
						</td>
						<td class="py-3 px-6 text-center w-[10%]">
							{{ \Carbon\Carbon::parse($election->end_time)->format('Hi') }}H
						</td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="py-6 text-center text-gray-500">No archive elections yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
		<div id="table-loading"
			class="hidden absolute inset-0 bg-white/60 backdrop-blur-[2px] flex items-center justify-center">
			<div class="w-10 h-10 border-4 border-gray-300 border-t-black rounded-full animate-spin"></div>
		</div>
  </div>

	<div class="flex items-center justify-end gap-x-5 px-4 py-3">
		<form id="per-page-form" method="GET" action="{{ route('admin.archives.index') }}"
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
			Showing {{ $elections->firstItem() ?? 0 }} – {{ $elections->lastItem() ?? 0 }} of {{ $elections->total() }}
		</div>

		<div id="pagination">
			{{ $elections->onEachSide(1)->links('vendor.pagination.always') }}
		</div>
	</div>
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

