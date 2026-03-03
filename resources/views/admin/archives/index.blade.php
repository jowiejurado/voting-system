@php($title = 'Archives Election | Voting System')

@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 px-10 pt-5">
  <div class="flex flex-col gap-y-5">
    <h1 class="text-2xl font-black text-[#0b252a]">Archives Election</h1>
    <div class="flex items-center justify-end gap-x-2">
      @php($hasFilters = !empty(request('start_date')) || !empty(request('end_date')) || !empty(request('start_time')) || !empty(request('end_time')) || !empty(request('created_at')) || !empty(request('month')) || !empty(request('year')))
      <button
        type="button"
        class="relative inline-flex items-center cursor-pointer justify-center w-10 h-10 text-gray-700"
        title="Open filters"
        aria-label="Open filters"
        data-modal-open="#archives-filter-modal"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
					<path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
				</svg>
        @if($hasFilters)
          <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-green-600 rounded-full border-2 border-white"></span>
        @endif
      </button>

      <form id="search-form" method="GET" action="{{ route('admin.archives.index') }}"
            class="flex items-center gap-x-2">
        <label for="search">Search:</label>
        <input id="search" name="q" type="search"
               value="{{ $q ?? '' }}"
               placeholder="Type keywords..."
               class="border-2 border-gray-300 py-1 px-2 outline-none"
               autofocus />
        {{-- Preserve current filters when searching --}}
        @if(!empty(request('start_date')))
          <input type="hidden" name="start_date" value="{{ request('start_date') }}">
        @endif
        @if(!empty(request('end_date')))
          <input type="hidden" name="end_date" value="{{ request('end_date') }}">
        @endif
        @if(!empty(request('start_time')))
          <input type="hidden" name="start_time" value="{{ request('start_time') }}">
        @endif
        @if(!empty(request('end_time')))
          <input type="hidden" name="end_time" value="{{ request('end_time') }}">
        @endif
        @if(!empty(request('created_at')))
          <input type="hidden" name="created_at" value="{{ request('created_at') }}">
        @endif
        @if(!empty(request('month')))
          <input type="hidden" name="month" value="{{ request('month') }}">
        @endif
        @if(!empty(request('year')))
          <input type="hidden" name="year" value="{{ request('year') }}">
        @endif
      </form>
    </div>
  </div>

  <div id="table-wrap" class="relative border-2 border-gray-400 rounded-3xl w-full overflow-hidden">
    <table class="table-fixed w-full" id="positions-table">
      <thead>
        <tr class="border-b-2 border-gray-400">
          <th class="py-3 px-6 text-center">Election Title</th>
          <th class="py-3 px-6 text-center w-[15%]">Start Date</th>
          <th class="py-3 px-6 text-center w-[10%]">Start Time</th>
          <th class="py-3 px-6 text-center w-[15%]">End Date</th>
          <th class="py-3 px-6 text-center w-[10%]">End Time</th>
          <th class="py-3 px-6 text-center w-[18%]">Created At</th>
          <th class="py-3 px-6 text-center w-[15%]">Tools</th>
        </tr>
      </thead>
      <tbody>
        @forelse($elections as $election)
          <tr class="border-b-2 border-gray-400 last:border-b-0">
            <td class="py-3 px-6 text-center">{{ $election->title }}</td>
            <td class="py-3 px-6 text-center w-[15%]">
              {{ \Carbon\Carbon::parse($election->start_date)->format('F d, Y') }}
            </td>
            <td class="py-3 px-6 text-center w-[10%]">
              {{ \Carbon\Carbon::parse($election->start_time)->format('Hi') }}H
            </td>
            <td class="py-3 px-6 text-center w-[15%]">
              {{ $election->end_date ? \Carbon\Carbon::parse($election->end_date)->format('F d, Y') : '-' }}
            </td>
            <td class="py-3 px-6 text-center w-[10%]">
              {{ \Carbon\Carbon::parse($election->end_time)->format('Hi') }}H
            </td>
            <td class="py-3 px-6 text-center w-[18%]">
              {{ optional($election->created_at)->timezone('Asia/Manila')->format('F d, Y H:i') }}
            </td>
            <td class="py-3 px-6 text-center w-[15%]">
              <a href="{{ route('admin.archives.show', $election->id) }}"
                 class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-full">
                View Result
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="py-6 text-center text-gray-500">No archive elections yet.</td>
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
      @if(!empty(request('start_date')))
        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
      @endif
      @if(!empty(request('end_date')))
        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
      @endif
      @if(!empty(request('start_time')))
        <input type="hidden" name="start_time" value="{{ request('start_time') }}">
      @endif
      @if(!empty(request('end_time')))
        <input type="hidden" name="end_time" value="{{ request('end_time') }}">
      @endif
      @if(!empty(request('created_at')))
        <input type="hidden" name="created_at" value="{{ request('created_at') }}">
      @endif
      @if(!empty(request('month')))
        <input type="hidden" name="month" value="{{ request('month') }}">
      @endif
      @if(!empty(request('year')))
        <input type="hidden" name="year" value="{{ request('year') }}">
      @endif
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
{{-- Filters Modal --}}
<x-ui.modal id="archives-filter-modal" title="Filter Archives">
  <form id="archives-filter-form" method="GET" action="{{ route('admin.archives.index') }}" class="space-y-4">
    {{-- Preserve existing params --}}
    <input type="hidden" name="q" value="{{ $q ?? '' }}">
    <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
    <input type="hidden" name="page" value="1"> {{-- reset to first page on apply --}}

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2">
        <label for="f_created_at" class="block text-sm font-medium text-gray-700 mb-1">Created at (date & time)</label>
        <input
          id="f_created_at"
          name="created_at"
          type="datetime-local"
          value="{{ request('created_at') }}"
          class="w-full border-2 border-gray-300 rounded-md py-2 px-3 outline-none"
        />
      </div>

      <div>
        <label for="f_start_date" class="block text-sm font-medium text-gray-700 mb-1">Start date</label>
        <input
          id="f_start_date"
          name="start_date"
          type="date"
          value="{{ request('start_date') }}"
          class="w-full border-2 border-gray-300 rounded-md py-2 px-3 outline-none"
        />
      </div>

      <div>
        <label for="f_start_time" class="block text-sm font-medium text-gray-700 mb-1">Start time</label>
        <input
          id="f_start_time"
          name="start_time"
          type="time"
          step="60"
          value="{{ request('start_time') }}"
          class="w-full border-2 border-gray-300 rounded-md py-2 px-3 outline-none"
        />
      </div>

      <div>
        <label for="f_end_date" class="block text-sm font-medium text-gray-700 mb-1">End date</label>
        <input
          id="f_end_date"
          name="end_date"
          type="date"
          value="{{ request('end_date') }}"
          class="w-full border-2 border-gray-300 rounded-md py-2 px-3 outline-none"
        />
      </div>

      <div>
        <label for="f_end_time" class="block text-sm font-medium text-gray-700 mb-1">End time</label>
        <input
          id="f_end_time"
          name="end_time"
          type="time"
          step="60"
          value="{{ request('end_time') }}"
          class="w-full border-2 border-gray-300 rounded-md py-2 px-3 outline-none"
        />
      </div>

      <div>
        <label for="f_month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
        <select id="f_month" name="month" class="w-full border-2 border-gray-300 rounded-md py-2 px-3 outline-none">
          <option value="">--</option>
          @foreach(range(1,12) as $m)
            <option value="{{ $m }}" @selected((int)request('month') === $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label for="f_year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
        <select id="f_year" name="year" class="w-full border-2 border-gray-300 rounded-md py-2 px-3 outline-none">
          <option value="">--</option>
          @foreach(range(2016, 2026) as $y)
            <option value="{{ $y }}" @selected((int)request('year') === $y)>{{ $y }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <p class="text-xs text-gray-500">Tip: Provide both start and end time to filter between times.</p>

    <div class="flex items-center justify-end gap-2 pt-2">
      <a
        href="{{ route('admin.archives.index', ['q' => $q ?? '', 'per_page' => $perPage ?? 10, 'page' => 1]) }}"
        class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100"
      >
        Reset Filters
      </a>
      <button type="button" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300" data-modal-cancel>
        Cancel
      </button>
      <button type="submit" class="px-4 py-2 rounded-md bg-black text-white">
        Apply Filters
      </button>
    </div>
  </form>
</x-ui.modal>
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

  // --- Trim empty filter inputs out of submission ---
  (function(){
    const f = document.getElementById('archives-filter-form');
    if (!f) return;
    f.addEventListener('submit', () => {
      ['start_date','end_date','start_time','end_time','created_at','month','year'].forEach((n) => {
        const el = f.querySelector(`[name="${n}"]`);
        if (el && String(el.value || '').trim() === '') {
          // prevent sending empty param
          el.removeAttribute('name');
        }
      });
    });
  })();
</script>
@endpush
