@php($title = 'Candidates | Voting System')

@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 px-3 sm:px-6 lg:px-10 pt-5">
  <div class="flex flex-col gap-y-5">
    <h1 class="text-2xl font-black text-[#0b252a]">Candidates</h1>
    <div class="flex flex-col gap-y-2 sm:flex-row sm:items-center sm:justify-between">
			<div class="flex flex-col gap-y-2">
				<button type="button" id="btn-add"
								class="bg-[#545454] hover:bg-[#686868] cursor-pointer px-6 py-2 rounded-full text-white w-fit"
								@if($lockedUpcomingElection)
									data-modal-open="#candidate-modal"
								@else
									data-modal-open="#add-candidate-unavailable-modal"
								@endif>
					Add Candidate
				</button>
			</div>

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

	<div id="table-wrap" class="relative border-2 border-gray-400 rounded-3xl w-full overflow-x-auto">
		<table class="table-fixed w-full min-w-[720px] text-sm sm:text-base" id="candidates-table">
      <thead>
        <tr class="border-b-2 border-gray-400">
					<th class="py-3 px-2 sm:px-6 text-center">Election</th>
          <th class="py-3 px-2 sm:px-6 text-center">Position</th>
          <th class="py-3 px-1 sm:px-3 text-center">Firstname</th>
					<th class="py-3 px-1 sm:px-3 text-center">Lastname</th>
					<th class="py-3 px-1 sm:px-3 text-center">Organization</th>
          <th class="w-48 sm:w-56 py-3 text-center">Tools</th>
        </tr>
      </thead>
      <tbody class="text-center">
        @forelse($candidates as $candidate)
          <tr class="border-b-2 border-gray-400 last:border-b-0">
						<td class="py-3 px-2 sm:px-6">{{ $candidate->election->title ?? '' }}</td>
            <td class="py-3 px-2 sm:px-6">{{ $candidate->position->name ?? '' }}</td>
						<td class="py-3 px-2 sm:px-6">{{ $candidate->first_name }}</td>
						<td class="py-3 px-2 sm:px-6">{{ $candidate->last_name }}</td>
            <td class="py-3 px-1 sm:px-3 text-center">{{ $candidate->organization?->name ?? '' }}</td>
            <td class="py-3 text-center">
              <div class="inline-flex flex-wrap items-center justify-center gap-2">
                <button type="button"
                        class="btn-edit bg-green-600 text-white px-3 py-[6px] text-sm rounded"
                        data-modal-open="#candidate-modal"
                        data-id="{{ $candidate->id }}"
                        data-first_name="{{ $candidate->first_name }}"
                        data-last_name="{{ $candidate->last_name }}"
                        data-organization="{{ $candidate->organization_id }}"
                        data-position="{{ $candidate->position_id }}"
                        data-election="{{ $candidate->election_id }}"
                        data-election-title="{{ $candidate->election->title ?? '' }}">
                  Edit
                </button>
                <button type="button"
                        class="btn-delete bg-red-600 hover:bg-red-700 text-white px-3 py-[6px] text-sm rounded"
                        data-delete-url="{{ route('admin.candidates.destroy', $candidate) }}"
                        data-delete-message="Delete candidate {{ $candidate->first_name }} {{ $candidate->last_name }}? This cannot be undone.">
                  Delete
                </button>
              </div>
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

	<div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center justify-end gap-3 sm:gap-x-5 px-4 py-3">
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

<x-ui.modal id="add-candidate-unavailable-modal"
            title="Cannot add candidate"
            size="max-w-md"
            :form="null">
	<p class="text-sm text-gray-700">
		You can add candidates once an election is within 10 days. Check back when the next election is closer.
	</p>
	@error('upcoming_election')
		<p class="text-red-600 text-sm mt-4" role="alert">{{ $message }}</p>
	@enderror
	<div class="flex justify-end mt-6">
		<button type="button" class="px-4 py-2 rounded-md bg-gray-800 text-white hover:bg-gray-900" data-modal-close>OK</button>
	</div>
</x-ui.modal>

<x-ui.modal id="candidate-modal"
            title="Add Candidate"
            :form="['id'=>'candidate-form','action'=>route('admin.candidates.store'),'method'=>'POST','submitText'=>'Submit']">
  <input type="hidden" name="_method" id="method-field" value="POST" data-clear-on-close>

	<div>
    <label class="block text-sm mb-1">Election</label>
		@if($lockedUpcomingElection)
			<input type="hidden" name="election" id="candidate-election" value="{{ old('election', $lockedUpcomingElection->id) }}">
			<div id="candidate-election-display"
				class="border-2 border-gray-400 py-2 px-2 w-full bg-gray-100 text-gray-700 rounded-sm select-none"
				role="status" aria-live="polite">{{ $lockedUpcomingElection->title }}</div>
			<p class="text-xs text-gray-500 mt-1">Upcoming election (ballot countdown within {{ $ballotCountdownDaysMax }} days) is fixed and cannot be changed.</p>
		@else
			<select name="election" id="candidate-election" class="border-2 border-gray-400 py-2 px-2 w-full">
				<option value="" disabled @selected(old('election') === null || old('election') === '')>Select election</option>
				@foreach($elections as $id => $election)
					<option value="{{ $id }}" @selected((string) old('election') === (string) $id)>{{ $election }}</option>
				@endforeach
			</select>
		@endif
    @error('election') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

	<div>
    <label class="block text-sm mb-1">Position</label>
   	<select name="position" id="position" required class="border-2 border-gray-400 py-2 px-2 w-full">
			<option value="" disabled @selected(old('position') === null || old('position') === '')>Select position</option>
			@foreach($positions as $id => $position)
				<option value="{{ $id }}" @selected((string) old('position') === (string) $id)>{{ $position }}</option>
			@endforeach
		</select>
    @error('position') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

	<div>
    <label class="block text-sm mb-1">Organization</label>
   	<select name="organization" id="organization" required class="border-2 border-gray-400 py-2 px-2 w-full">
			<option value="" disabled @selected(old('organization') === null || old('organization') === '')>Select organization</option>
			@foreach($organizations as $id => $organization)
				<option value="{{ $id }}" @selected((string) old('organization') === (string) $id)>{{ $organization }}</option>
			@endforeach
		</select>
    @error('organization') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
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

	@error('candidate_duplicate')
		<p class="text-red-600 text-sm mt-2" role="alert">{{ $message }}</p>
	@enderror
</x-ui.modal>

<x-ui.modal id="delete-confirm-modal"
            title="Confirm deletion"
            size="max-w-md"
            :form="null">
  <p id="delete-confirm-message" class="text-sm text-gray-700"></p>
  <form id="delete-confirm-form" method="POST" class="mt-6" action="#">
    @csrf
    @method('DELETE')
    <div class="flex items-center justify-end gap-2">
      <button type="button" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300" data-modal-cancel>Cancel</button>
      <button type="submit" class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white">Delete</button>
    </div>
  </form>
</x-ui.modal>

<meta name="candidate-update-url" content="{{ route('admin.candidates.update', ':id') }}">
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

  const candidateModal 	= document.getElementById('candidate-modal');
  const candidateForm  	= document.getElementById('candidate-form');
  const methodField   	= document.getElementById('method-field');
  const modalTitleEl  	= candidateModal.querySelector('[data-modal-title]');
  const submitBtn     	= candidateModal.querySelector('[data-modal-submit]');

  const firstNameInp       	= document.getElementById('first_name');
  const lastNameInp        	= document.getElementById('last_name');
  const organizationInp   	= document.getElementById('organization');
  const positionInp        	= document.getElementById('position');
	const electionInp       	= document.getElementById('candidate-election');
	const electionDisplayEl	= document.getElementById('candidate-election-display');
	const electionLocked    	= @json((bool) $lockedUpcomingElection);
	const lockedElectionId  	= @json($lockedUpcomingElection ? (string) $lockedUpcomingElection->id : '');
	const lockedElectionTitle	= @json($lockedUpcomingElection ? (string) $lockedUpcomingElection->title : '');

	function setElectionField(electionId, electionTitle) {
		if (!electionInp) return;
		electionInp.value = electionId || '';
		if (typeof electionInp.defaultValue === 'string') {
			electionInp.defaultValue = electionId || '';
		}
		if (electionDisplayEl) {
			electionDisplayEl.textContent = electionTitle || '';
		}
	}

  document.addEventListener('click', (e) => {
    if (e.target.closest('#btn-add')) {
      if (!electionLocked) {
        return;
      }
      candidateForm.action = @json(route('admin.candidates.store'));
      methodField.value   = 'POST';
      modalTitleEl.textContent = 'Add Candidate';
      submitBtn.textContent = 'Submit';
      firstNameInp.value = '';
      lastNameInp.value  = '';
			if (organizationInp) organizationInp.value = '';
			if (electionLocked) {
				setElectionField(lockedElectionId, lockedElectionTitle);
			} else if (electionInp) {
				electionInp.value = '';
			}
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
			if (organizationInp) organizationInp.value = editBtn.dataset.organization || '';
			const eid = editBtn.dataset.election || '';
			const etitle = editBtn.dataset.electionTitle || '';
			if (electionLocked) {
				setElectionField(eid, (etitle || lockedElectionTitle));
			} else if (electionInp) {
				electionInp.value = eid;
			}
			positionInp.value = editBtn.dataset.position || '';
      return;
    }
  });

  @if($openCandidateModalAfterError || $openUnavailableModalAfterError)
  (function () {
    var modalId = @json($openCandidateModalAfterError ? 'candidate-modal' : 'add-candidate-unavailable-modal');
    function openWhenReady() {
      var n = 0;
      function tick() {
        if (typeof window.Modal !== 'undefined' && typeof window.Modal.openById === 'function') {
          window.Modal.openById(modalId);
          return;
        }
        n++;
        if (n < 120) {
          requestAnimationFrame(tick);
        }
      }
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { requestAnimationFrame(tick); });
      } else {
        requestAnimationFrame(tick);
      }
    }
    openWhenReady();
  })();
  @endif

  (function () {
    const delForm = document.getElementById('delete-confirm-form');
    const delMsg = document.getElementById('delete-confirm-message');
    if (!delForm || !delMsg) return;
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-delete');
      if (!btn) return;
      const url = btn.dataset.deleteUrl;
      if (url) delForm.action = url;
      delMsg.textContent = btn.dataset.deleteMessage || 'Are you sure you want to delete this record? This cannot be undone.';
      window.Modal.openById('delete-confirm-modal');
    });
    delForm.addEventListener('submit', () => { showTableLoading(); });
  })();
</script>
@endpush

