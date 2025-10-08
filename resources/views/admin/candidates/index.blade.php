@php($title = 'Candidates | Voting System')

@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 px-10 pt-5">
  <div class="flex flex-col gap-y-5">
    <h1 class="text-2xl font-black text-[#0b252a]">Candidates</h1>
    <div class="flex items-center justify-between">
			<button type="button" id="btn-add"
							class="bg-[#545454] hover:bg-[#686868] cursor-pointer px-6 py-2 rounded-full text-white"
							data-modal-open="#candidate-modal">
				Add Candidate
			</button>

			<form id="search-form" method="GET" action="{{ route('admin.candidates.index') }}"
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
		<table class="table-fixed w-full" id="candidates-table">
      <thead>
        <tr class="border-b-2 border-gray-400">
					<th class="py-3 px-6 text-center">Election</th>
          <th class="py-3 px-6 text-center">Position</th>
          <th class="py-3 text-center">Firstname</th>
					<th class="py-3 text-center">Lastname</th>
					<th class="py-3 text-center">Organization</th>
          <th class="w-56 py-3 text-center">Tools</th>
        </tr>
      </thead>
      <tbody class="text-center">
        @forelse($candidates as $candidate)
          <tr class="border-b-2 border-gray-400 last:border-b-0">
						<td class="py-3 px-6">{{ $candidate->election->title ?? '' }}</td>
            <td class="py-3 px-6">{{ $candidate->position->name ?? '' }}</td>
						<td class="py-3 px-6">{{ $candidate->first_name }}</td>
						<td class="py-3 px-6">{{ $candidate->last_name }}</td>
            <td class="py-3 text-center">{{ $candidate->organization_name }}</td>
            <td class="py-3 text-center">
              <button type="button"
											class="btn-edit bg-green-600 text-white px-3 py-[6px] text-sm rounded"
											data-modal-open="#candidate-modal"
											data-id="{{ $candidate->id }}"
											data-first_name="{{ $candidate->first_name }}"
											data-last_name="{{ $candidate->last_name }}"
											data-organization_name="{{ $candidate->organization_name }}"
											data-position="{{ $candidate->position_id }}"
											data-election="{{ $candidate->election_id }}">
								Edit
							</button>

							<button type="button"
											class="btn-delete bg-red-600 text-white px-3 py-1.5 text-sm rounded"
											data-modal-open="#delete-modal"
											data-id="{{ $candidate->id }}"
											data-firstname="{{ $candidate->first_name }}"
											data-lastname="{{ $candidate->last_name }}">
								Delete
							</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="py-6 text-center text-gray-500">No candidates yet.</td>
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
		<form id="per-page-form" method="GET" action="{{ route('admin.candidates.index') }}"
					class="flex gap-x-2 items-center">
			<label class="text-sm text-gray-600">Items per page:</label>
			<input type="hidden" name="q" value="{{ $q }}">
			<input type="hidden" name="page" value="1">
			<select name="per_page" class="border-2 border-gray-300 py-1 px-2"
							onchange="this.form.submit()">
				@foreach([5,10,15,25,50] as $n)
					<option value="{{ $n }}" @selected(($perPage ?? 10) == $n)>{{ $n }}</option>
				@endforeach
			</select>
		</form>

		<div class="text-sm text-gray-600">
			Showing {{ $candidates->firstItem() ?? 0 }} – {{ $candidates->lastItem() ?? 0 }} of {{ $candidates->total() }}
		</div>

		<div id="pagination">
			{{ $candidates->onEachSide(1)->links('vendor.pagination.always') }}
		</div>
	</div>
</div>

<x-ui.modal id="candidate-modal"
            title="Add Candidate"
            :form="['id'=>'candidate-form','action'=>route('admin.candidates.store'),'method'=>'POST','submitText'=>'Submit']">
  <input type="hidden" name="_method" id="method-field" value="POST" data-clear-on-close>

	<div>
    <label class="block text-sm mb-1">Election</label>
   	<select name="election" id="election" class="border-2 border-gray-400 py-2 px-2 w-full">
			<option value="" disabled selected>Select election</option>
			@foreach($elections as $id => $election)
				<option value="{{ old('election') ?? $id }}">{{ $election }}</option>
			@endforeach
		</select>
    @error('election') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

	<div>
    <label class="block text-sm mb-1">Position</label>
   	<select name="position" id="position" class="border-2 border-gray-400 py-2 px-2 w-full">
			<option value="" disabled selected>Select position</option>
			@foreach($positions as $id => $position)
				<option value="{{ old('position') ?? $id }}">{{ $position }}</option>
			@endforeach
		</select>
    @error('position') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm mb-1">First Name</label>
    <input type="text" name="first_name" id="first_name"
           class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
           value="{{ old('first_name') }}" placeholder="e.g., Juan" required>
    @error('first_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

	<div>
    <label class="block text-sm mb-1">Last Name</label>
    <input type="text" name="last_name" id="last_name"
           class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
           value="{{ old('last_name') }}" placeholder="e.g., Dela Cruz" required>
    @error('last_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

	<div>
    <label class="block text-sm mb-1">Organization Name</label>
    <input type="text" name="organization_name" id="organization_name"
           class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
           value="{{ old('organization_name') }}" placeholder="e.g., IT, Marketing" required>
    @error('organization_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <x-ui.admin-auth class="pt-2" />
</x-ui.modal>

<x-ui.modal id="delete-modal"
            title="Delete Candidate"
            :form="['id'=>'delete-form','action'=>'','method'=>'POST','spoof'=>'DELETE','submitText'=>'Delete']"
            size="max-w-[520px]">
  <input type="hidden" name="__action" value="delete" data-clear-on-close>
  <input type="hidden" name="__delete_id" id="__delete_id" data-clear-on-close>
  <input type="hidden" name="__delete_first_name" id="__delete_first_name" data-clear-on-close>
	<input type="hidden" name="__delete_last_name" id="__delete_last_name" data-clear-on-close>

  <p class="text-xl text-center font-semibold">
    Are you sure you want to delete
    <span class="font-black text-red-500" id="del-candidate-name">this candidate</span>?
  </p>

  <x-ui.admin-auth class="pt-2" />
</x-ui.modal>

<meta name="candidate-update-url" content="{{ route('admin.candidates.update', ':id') }}">
<meta name="candidate-delete-url" content="{{ route('admin.candidates.destroy', ':id') }}">
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
  const updateTpl = document.querySelector('meta[name="candidate-update-url"]').content;
  const deleteTpl = document.querySelector('meta[name="candidate-delete-url"]').content;

  const candidateModal 	= document.getElementById('candidate-modal');
  const candidateForm  	= document.getElementById('candidate-form');
  const methodField   	= document.getElementById('method-field');
  const modalTitleEl  	= candidateModal.querySelector('[data-modal-title]');
  const submitBtn     	= candidateModal.querySelector('[data-modal-submit]');

  const firstNameInp       	= document.getElementById('first_name');
  const lastNameInp        	= document.getElementById('last_name');
	const orgNameInp       		= document.getElementById('organization_name');
  const positionInp        	= document.getElementById('position');
	const electionInp       	= document.getElementById('election');

  document.addEventListener('click', (e) => {
    if (e.target.closest('#btn-add')) {
      candidateForm.action = @json(route('admin.candidates.store'));
      methodField.value   = 'POST';
      modalTitleEl.textContent = 'Add Candidate';
      submitBtn.textContent = 'Submit';
      firstNameInp.value = '';
      lastNameInp.value  = '';
			orgNameInp.value = '';
      electionInp.value  = '';
			positionInp.value = '';
      return;
    }

    const editBtn = e.target.closest('.btn-edit');
    if (editBtn) {
      const id  = editBtn.dataset.id;
      const url = updateTpl.replace(':id', id);

      candidateForm.action = url;
      methodField.value   = 'PUT';
      modalTitleEl.textContent = 'Edit Candidate';
      submitBtn.textContent = 'Update';

			firstNameInp.value = editBtn.dataset.first_name || '';
      lastNameInp.value  = editBtn.dataset.last_name || '';
			orgNameInp.value = editBtn.dataset.organization_name || '';
      electionInp.value  = editBtn.dataset.election || '';
			positionInp.value = editBtn.dataset.position || '';
      return;
    }
  });

  const deleteModal = document.getElementById('delete-modal');
  const deleteForm  = document.getElementById('delete-form');
  const delFullNameSpan = document.getElementById('del-candidate-name');
  const delIdHidden = document.getElementById('__delete_id');
  const delLastNameHidden = document.getElementById('__delete_last_name');
	const delFirstNameHidden = document.getElementById('__delete_first_name');

  document.addEventListener('click', (e) => {
    const delBtn = e.target.closest('.btn-delete');
    if (!delBtn) return;

    const id   = delBtn.dataset.id;
    const name = `${delBtn.dataset.firstname} ${delBtn.dataset.lastname}` || 'this candidate';

    deleteForm.action = deleteTpl.replace(':id', id);
    delIdHidden.value = id;
    delLastNameHidden.value = delBtn.dataset.lastname;
		delFirstNameHidden.value = delBtn.dataset.firstname;
    delFullNameSpan.textContent = name;
  });

  @if($errors->any() && old('__action') === 'delete')
    window.Modal.openById('delete-modal');
    deleteModal.querySelector('input[name="password"]').value = '';
  @endif

  @if($errors->any() && old('__action') !== 'delete')
    window.Modal.openById('candidate-modal');
  @endif
</script>
@endpush

