@php($title = 'Positions | Voting System')

@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 px-10 pt-10">
  <div class="flex flex-col gap-y-10">
    <h1 class="text-2xl font-black text-[#0b252a]">Positions</h1>
    <div class="flex items-center justify-between">
      <button type="button" id="btn-add"
              class="bg-[#545454] hover:bg-[#686868] cursor-pointer px-6 py-2 rounded-full text-white">
        Add Position
      </button>
      <div class="flex items-center gap-x-4">
        <label>Search:</label>
        <input id="search" type="text" placeholder="Type keywords..." class="border-2 border-gray-300 py-1 px-2 outline-none"/>
      </div>
    </div>
  </div>

  <div class="border-2 border-gray-400 rounded-3xl w-full overflow-hidden">
    <table class="table-fixed w-full" id="positions-table">
      <thead>
        <tr class="border-b-2 border-gray-400">
          <th class="py-3 px-6 text-left">Position</th>
          <th class="w-28 py-3 text-center">Maximum Votes</th>
          <th class="w-56 py-3 text-center">Tools</th>
        </tr>
      </thead>
      <tbody>
        @forelse($positions as $position)
          <tr class="border-b-2 border-gray-400 last:border-b-0">
            <td class="py-3 px-6">{{ $position->name }}</td>
            <td class="py-3 text-center">{{ $position->maximum_votes }}</td>
            <td class="py-3 text-center">
              <button type="button"
                      class="btn-edit bg-green-600 text-white px-3 py-[6px] text-sm rounded"
                      data-id="{{ $position->id }}"
                      data-name="{{ $position->name }}"
                      data-maximum_votes="{{ $position->maximum_votes }}">
                Edit
              </button>

              <button type="button"
											class="btn-delete bg-red-600 text-white px-3 py-1.5 text-sm rounded"
											data-id="{{ $position->id }}"
											data-name="{{ $position->name }}">
								Delete
							</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="py-6 text-center text-gray-500">No positions yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div id="position-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-4">
  <div class="w-full max-w-[560px] rounded-2xl bg-white shadow-xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <h3 id="modal-title" class="text-lg font-bold">Add Position</h3>
      <button type="button" id="btn-close" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>

    <form id="position-form" method="post" action="{{ route('admin.positions.store') }}" class="px-5 py-4 space-y-4">
      @csrf
      <input type="hidden" name="_method" id="method-field" value="POST">

      <div>
        <label class="block text-sm mb-1">Position</label>
        <input type="text" name="name" id="name"
               class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
               value="{{ old('name') }}" placeholder="e.g., President" required>
        @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm mb-1">Maximum Votes</label>
        <input type="number" min="1" name="maximum_votes" id="maximum_votes"
               class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
               value="{{ old('maximum_votes', 1) }}" required>
        @error('maximum_votes') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

			<h3 id="modal-title" class="text-lg font-bold">Admin Authentication</h3>

			<div>
        <label class="block text-sm mb-1">Admin ID</label>
        <input type="text"
							 name="admin_id"
							 id="admin_id"
               class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
							 required
							 autocomplete="off" autocapitalize="none" autocorrect="off" spellcheck="false">
        @error('admin_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm mb-1">Password</label>
        <input type="password"
							 id="password"
							 name="password"
               class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
							 required
							 autocomplete="off" autocapitalize="none" autocorrect="off" spellcheck="false">
        @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" id="btn-cancel" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 cursor-pointer">
          Cancel
        </button>
        <button type="submit" id="btn-submit" class="px-4 py-2 rounded-md bg-black text-white cursor-pointer">
          Submit
        </button>
      </div>
    </form>
  </div>
</div>

<div id="delete-modal" class="fixed inset-0 z-[110] hidden items-center justify-center bg-black/50 p-4">
  <div class="w-full max-w-[520px] rounded-2xl bg-white shadow-xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <h3 class="text-lg font-bold">Delete Position</h3>
      <button type="button" id="btn-del-close" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>

    <form id="delete-form" method="post" action="" class="px-5 py-4 space-y-4" autocomplete="off">
      @csrf
      @method('DELETE')
      <input type="hidden" name="__action" value="delete">
      <input type="hidden" name="__delete_id" id="__delete_id">
      <input type="hidden" name="__delete_name" id="__delete_name">

      <p class="text-2xl font-extrabold">
        Are you sure you want to delete
        <span class="font-semibold" id="del-position-name">this position</span>?
      </p>

      <h4 class="text-base font-bold pt-1">Admin Authentication</h4>

      <div>
        <label class="block text-sm mb-1">Admin ID</label>
        <input type="text" name="admin_id" id="del_admin_id"
					class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
					autocomplete="off" autocapitalize="none" autocorrect="off" spellcheck="false" required>
        @error('admin_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm mb-1">Password</label>
        <input type="password" name="password" id="del_password"
					class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
					autocomplete="new-password" required>
        @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" id="btn-del-cancel" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-white cursor-pointer">
          Delete
        </button>
      </div>
    </form>
  </div>
</div>

<meta name="position-update-url" content="{{ route('admin.positions.update', ':id') }}">
<meta name="position-delete-url" content="{{ route('admin.positions.destroy', ':id') }}">
@endsection

@push('scripts')
<script>
  const search = document.getElementById('search');
  const table = document.getElementById('positions-table')?.getElementsByTagName('tbody')[0];
  if (search && table) {
    search.addEventListener('input', () => {
      const q = search.value.toLowerCase();
      Array.from(table.rows).forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  const modal       = document.getElementById('position-modal');
  const btnAdd      = document.getElementById('btn-add');
  const btnClose    = document.getElementById('btn-close');
  const btnCancel   = document.getElementById('btn-cancel');
  const form        = document.getElementById('position-form');
  const methodField = document.getElementById('method-field');
  const titleEl     = document.getElementById('modal-title');
  const submitBtn   = document.getElementById('btn-submit');

	const delModal    = document.getElementById('delete-modal');
const delForm       = document.getElementById('delete-form');
const delClose      = document.getElementById('btn-del-close');
const delCancel     = document.getElementById('btn-del-cancel');
const delNameSpan   = document.getElementById('del-position-name');
const delIdHidden   = document.getElementById('__delete_id');
const delNameHidden = document.getElementById('__delete_name');

  const nameInp = document.getElementById('name');
  const maxInp  = document.getElementById('maximum_votes');

	const open  = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
	const close = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };
	const openFlex  = el => { el.classList.remove('hidden'); el.classList.add('flex'); };
	const closeFlex = el => { el.classList.add('hidden'); el.classList.remove('flex'); };
  const resetForm = () => form.reset();

	function resetDeleteForm() {
		delForm.action = '';
		delIdHidden.value = '';
		delNameHidden.value = '';
		delNameSpan.textContent = 'this position';

		delForm.reset();
		delForm.querySelectorAll('input[type="text"], input[type="password"]').forEach(i => i.value = '');
	}

	function openDeleteModalFor(id, name) {
		resetDeleteForm();
		const routeTpl = document.querySelector('meta[name="position-delete-url"]').content;
		delForm.action = routeTpl.replace(':id', id);
		delIdHidden.value = id;
		delNameHidden.value = name;
		delNameSpan.textContent = name || 'this position';
		openFlex(delModal);
	}

	function closeDeleteModal() {
		closeFlex(delModal);
		resetDeleteForm();
	}

  btnAdd?.addEventListener('click', () => {
    resetForm();
    form.action = "{{ route('admin.positions.store') }}";
    methodField.value = "POST";
    titleEl.textContent = "Add Position";
    submitBtn.textContent = "Submit";
    open();
  });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-edit');
    if (!btn) return;

    const id   = btn.dataset.id;
    const name = btn.dataset.name || '';
    const max  = btn.dataset.maximum_votes || 1;

    const template = document.querySelector('meta[name="position-update-url"]').content;
    const actionUrl = template.replace(':id', id);

    form.action = actionUrl;
    methodField.value = "PUT";
    titleEl.textContent = "Edit Position";
    submitBtn.textContent = "Update";

    nameInp.value = name;
    maxInp.value  = max;

    open();
  });

  btnClose?.addEventListener('click', close);
  btnCancel?.addEventListener('click', close);

	document.addEventListener('click', (e) => {
		const btn = e.target.closest('.btn-delete');
		if (!btn) return;
		openDeleteModalFor(btn.dataset.id, btn.dataset.name);
	});

 	delClose?.addEventListener('click', closeDeleteModal);
	delCancel?.addEventListener('click', closeDeleteModal);

	delModal?.addEventListener('click', (e) => {
		if (e.target === delModal) closeDeleteModal();
	});

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && delModal.classList.contains('flex')) closeDeleteModal();
	});

  @if($errors->any())
    open();
  @endif

	@if($errors->any() && old('__action') === 'delete')
		(function(){
			const id   = @json(old('__delete_id'));
			const name = @json(old('__delete_name')) || 'this position';
			if (id) {
				const routeTpl = document.querySelector('meta[name="position-delete-url"]').content;
				delForm.action = routeTpl.replace(':id', id);
			}
			delNameSpan.textContent = name;
			openFlex(delModal);
			const pwd = document.getElementById('del_password');
			if (pwd) pwd.value = '';
		})();
	@endif
</script>
@endpush
