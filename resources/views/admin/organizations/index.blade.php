@php($title = 'Organizations | Voting System')

@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 px-3 sm:px-6 lg:px-10 pt-5">
  <div class="flex flex-col gap-y-5">
    <h1 class="text-2xl font-black text-[#0b252a]">Organizations</h1>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
			<button type="button" id="btn-add"
							class="bg-[#545454] hover:bg-[#686868] cursor-pointer px-6 py-2 rounded-full text-white"
							data-modal-open="#organization-modal">
				Add Organization
			</button>

			<form id="search-form" method="GET" action="{{ route('admin.organizations.index') }}"
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
		<table class="table-fixed w-full min-w-[640px] text-sm sm:text-base" id="organizations-table">
      <thead>
        <tr class="border-b-2 border-gray-400">
          <th class="py-3 px-6 text-left">Organization</th>
          <th class="w-56 py-3 text-center">Tools</th>
        </tr>
      </thead>
      <tbody>
        @forelse($organizations as $organization)
          <tr class="border-b-2 border-gray-400 last:border-b-0">
            <td class="py-3 px-6">{{ $organization->name }}</td>
            <td class="py-3 text-center">
              <div class="inline-flex flex-wrap items-center justify-center gap-2">
                <button type="button"
                        class="btn-edit bg-green-600 text-white px-3 py-[6px] text-sm rounded"
                        data-modal-open="#organization-modal"
                        data-id="{{ $organization->id }}"
                        data-name="{{ $organization->name }}">
                  Edit
                </button>
                <button type="button"
                        class="btn-delete bg-red-600 hover:bg-red-700 text-white px-3 py-[6px] text-sm rounded"
                        data-delete-url="{{ route('admin.organizations.destroy', $organization) }}"
                        data-delete-message="Delete this organization ({{ $organization->name }})? Related candidates will be removed. This cannot be undone.">
                  Delete
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="2" class="py-6 text-center text-gray-500">No organizations yet.</td>
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
		<form id="per-page-form" method="GET" action="{{ route('admin.organizations.index') }}"
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
			Showing {{ $organizations->firstItem() ?? 0 }} – {{ $organizations->lastItem() ?? 0 }} of {{ $organizations->total() }}
		</div>

		<div id="pagination">
			{{ $organizations->onEachSide(1)->links('vendor.pagination.always') }}
		</div>
	</div>
</div>

<x-ui.modal id="organization-modal"
            title="Add Organization"
            :form="['id'=>'organization-form','action'=>route('admin.organizations.store'),'method'=>'POST','submitText'=>'Submit']">
  <input type="hidden" name="_method" id="method-field" value="POST" data-clear-on-close>

  <div>
    <label class="block text-sm mb-1">Organization</label>
    <input type="text" name="name" id="name"
           class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
           value="{{ old('name') }}" placeholder="e.g., IT Department" required>
    @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  {{-- <x-ui.admin-auth class="pt-2" /> --}}
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

<meta name="organization-update-url" content="{{ route('admin.organizations.update', ':id') }}">
@endsection

@push('scripts')
<script>
	function showTableLoading() {
    const el = document.getElementById('table-loading');
    if (el) el.classList.remove('hidden');
  }
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
    });
  })();

  (function(){
    const pager = document.getElementById('pagination');
    if (!pager) return;
    pager.addEventListener('click', (e) => {
      const a = e.target.closest('a');
      if (!a) return;
      showTableLoading();
    });
  })();

  const updateTpl = document.querySelector('meta[name="organization-update-url"]').content;

  const organizationModal = document.getElementById('organization-modal');
  const organizationForm  = document.getElementById('organization-form');
  const methodField   = document.getElementById('method-field');
  const modalTitleEl  = organizationModal.querySelector('[data-modal-title]');
  const submitBtn     = organizationModal.querySelector('[data-modal-submit]');
  const nameInp       = document.getElementById('name');

  document.addEventListener('click', (e) => {
    if (e.target.closest('#btn-add')) {
      organizationForm.action = @json(route('admin.organizations.store'));
      methodField.value   = 'POST';
      modalTitleEl.textContent = 'Add Organization';
      submitBtn.textContent = 'Submit';
      nameInp.value = '';
      return;
    }

    const editBtn = e.target.closest('.btn-edit');
    if (editBtn) {
      const id  = editBtn.dataset.id;
      const url = updateTpl.replace(':id', id);

      organizationForm.action = url;
      methodField.value   = 'PUT';
      modalTitleEl.textContent = 'Edit Organization';
      submitBtn.textContent = 'Update';

      nameInp.value = editBtn.dataset.name || '';
      return;
    }
  });

  @if($errors->any())
    window.Modal.openById('organization-modal');
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
