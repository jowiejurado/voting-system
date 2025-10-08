@php($title = 'Upcoming Elections | Voting System')

@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 px-10 pt-5">
  <div class="flex flex-col gap-y-5">
    <h1 class="text-2xl font-black text-[#0b252a]">Upcoming Elections</h1>
    <div class="flex items-center justify-between">
			<button type="button" id="btn-add"
							class="bg-[#545454] hover:bg-[#686868] cursor-pointer px-6 py-2 rounded-full text-white"
							data-modal-open="#election-modal">
				Add Election
			</button>

			<form id="search-form" method="GET" action="{{ route('admin.elections.index') }}"
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
          <th class="w-56 py-3 text-center">Tools</th>
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
            <td class="py-3 text-center">
              <button type="button"
											class="btn-edit bg-green-600 text-white px-3 py-[6px] text-sm rounded"
											data-modal-open="#election-modal"
											data-id="{{ $election->id }}"
											data-title="{{ $election->title }}"
											data-date="{{ \Carbon\Carbon::parse($election->date)->format('Y-m-d') }}"
											data-start_time="{{ \Carbon\Carbon::parse($election->start_time)->format('H:i:s') }}"
  										data-end_time="{{ \Carbon\Carbon::parse($election->end_time)->format('H:i:s') }}">
								Edit
							</button>

							<button type="button"
											class="btn-delete bg-red-600 text-white px-3 py-1.5 text-sm rounded"
											data-modal-open="#delete-modal"
											data-id="{{ $election->id }}"
											data-title="{{ $election->title }}">
								Delete
							</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="py-6 text-center text-gray-500">No elections yet.</td>
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
		<form id="per-page-form" method="GET" action="{{ route('admin.elections.index') }}"
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

<x-ui.modal id="election-modal"
            title="Add Election"
            :form="['id'=>'election-form','action'=>route('admin.elections.store'),'method'=>'POST','submitText'=>'Submit']">
  <input type="hidden" name="_method" id="method-field" value="POST" data-clear-on-close>

  <div>
    <label class="block text-sm mb-1">Title</label>
    <input type="text" name="title" id="title"
           class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
           value="{{ old('title') }}" placeholder="e.g., 2025 Election" required>
    @error('title') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

	<div>
    <label class="block text-sm mb-1">Date</label>
    <input type="date" name="date" id="date"
           class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
           value="{{ old('date') }}" required>
    @error('date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

	<div>
		<label class="block text-sm mb-1">Starting Time</label>
		<input
			type="time"
			name="start_time"
			id="start_time"
			class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
			step="1"
			value="{{ old('start_time') }}"
			required
		>
		@error('start_time') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
	</div>

	<div>
    <label class="block text-sm mb-1">Time Ended</label>
    <input type="time" name="end_time" id="end_time" step="1"
           class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
           value="{{ old('end_time') }}" required>
    @error('end_time') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <x-ui.admin-auth class="pt-2" />
</x-ui.modal>

<x-ui.modal id="delete-modal"
            title="Delete Position"
            :form="['id'=>'delete-form','action'=>'','method'=>'POST','spoof'=>'DELETE','submitText'=>'Delete']"
            size="max-w-[520px]">
  <input type="hidden" name="__action" value="delete" data-clear-on-close>
  <input type="hidden" name="__delete_id" id="__delete_id" data-clear-on-close>
  <input type="hidden" name="__delete_title" id="__delete_title" data-clear-on-close>

  <p class="text-xl text-center font-semibold">
    Are you sure you want to delete
    <span class="font-black text-red-500" id="del-election-title">this election</span>?
  </p>

  <x-ui.admin-auth class="pt-2" />
</x-ui.modal>

<meta name="election-update-url" content="{{ route('admin.elections.update', ':id') }}">
<meta name="election-delete-url" content="{{ route('admin.elections.destroy', ':id') }}">
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

  // ---- Your existing modal wiring below (unchanged) ----
  const updateTpl = document.querySelector('meta[name="election-update-url"]').content;
  const deleteTpl = document.querySelector('meta[name="election-delete-url"]').content;

  const electionModal = document.getElementById('election-modal');
  const electionForm  = document.getElementById('election-form');
  const methodField   = document.getElementById('method-field');
  const modalTitleEl  = electionModal.querySelector('[data-modal-title]');
  const submitBtn     = electionModal.querySelector('[data-modal-submit]');
  const titleInp      = document.getElementById('title');
	const dateInp       = document.getElementById('date');
	const startTimeInp  = document.getElementById('start_time');
	const endTimeInp    = document.getElementById('end_time');


  document.addEventListener('click', (e) => {
    if (e.target.closest('#btn-add')) {
      electionForm.action = @json(route('admin.elections.store'));
      methodField.value   = 'POST';
      modalTitleEl.textContent = 'Add Election';
      submitBtn.textContent = 'Submit';
      titleInp.value = '';
			titleInp.value = '';
			dateInp.value = '';
			startTimeInp.value = '';
			endTimeInp.value = '';
      return;
    }

    const editBtn = e.target.closest('.btn-edit');
    if (editBtn) {
      const id  = editBtn.dataset.id;
      const url = updateTpl.replace(':id', id);

      electionForm.action = url;
      methodField.value   = 'PUT';
      modalTitleEl.textContent = 'Edit Election';
      submitBtn.textContent = 'Update';

      titleInp.value = editBtn.dataset.title || '';
			dateInp.value = editBtn.dataset.date || '';
			startTimeInp.value = editBtn.dataset.start_time || '';
			endTimeInp.value = editBtn.dataset.end_time || '';
      return;
    }
  });

  const deleteModal = document.getElementById('delete-modal');
  const deleteForm  = document.getElementById('delete-form');
  const delTitleSpan = document.getElementById('del-election-title');
  const delIdHidden = document.getElementById('__delete_id');
  const delTitleHidden = document.getElementById('__delete_title');

  document.addEventListener('click', (e) => {
    const delBtn = e.target.closest('.btn-delete');
    if (!delBtn) return;

    const id   = delBtn.dataset.id;
    const title = delBtn.dataset.title || 'this election';

    deleteForm.action = deleteTpl.replace(':id', id);
    delIdHidden.value = id;
    delTitleHidden.value = title;
    delTitleSpan.textContent = title;
  });

  @if($errors->any() && old('__action') === 'delete')
    window.Modal.openById('delete-modal');
    deleteModal.querySelector('input[name="password"]').value = '';
  @endif

  @if($errors->any() && old('__action') !== 'delete')
    window.Modal.openById('election-modal');
  @endif
</script>
@endpush

