@php($title = 'Admin | Voting System')

@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 px-10 pt-5">
  <div class="flex flex-col gap-y-5">
    <h1 class="text-2xl font-black text-[#0b252a]">Admin</h1>
    <div class="flex items-center">

			@if (Auth::user() && Auth::user()->type == 'system-admin')
				<button
					type="button" id="btn-add"
					class="bg-[#545454] hover:bg-[#686868] cursor-pointer px-6 py-2 rounded-full text-white"
					data-modal-open="#admin-modal">
					Add Admin
				</button>
			@endif

			<form id="search-form" method="GET" action="{{ route('admin.index') }}"
						class="flex items-center gap-x-2 ml-auto">
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
		<table class="table-fixed w-full" id="admins-table">
      <thead>
        <tr class="border-b-2 border-gray-400">
					<th class="py-3 text-center">Last Name</th>
          <th class="py-3 text-center">First Name</th>
					<th class="py-3 text-center">Admin ID</th>
					<th class="py-3 text-center">Last Signed In & Out</th>
          <th class="w-56 py-3 text-center">Tools</th>
        </tr>
      </thead>
      <tbody class="text-center">
        @forelse($admins as $admin)
          <tr class="border-b-2 border-gray-400 last:border-b-0">
						<td class="py-3 px-6">{{ $admin->last_name }}</td>
						<td class="py-3 px-6">{{ $admin->first_name }}</td>
						<td class="py-3 px-6">{{ $admin->admin_id }}</td>
						<td class="py-3 px-6 flex flex-col items-center text-center">
							<span>
								{{ $admin->last_signed_in ? 'IN - ' . optional($admin->last_signed_in)->format('d/m/Y Hi') . 'H' : '' }}
							</span>
							<span>
								{{ $admin->last_signed_out ? 'OUT - ' . optional($admin->last_signed_out)->format('d/m/Y Hi') . 'H' : '' }}
							</span>
						</td>
            <td class="py-3 text-center">
              <button type="button"
											class="btn-edit bg-green-600 text-white px-3 py-[6px] text-sm rounded"
											data-modal-open="#admin-modal"
											data-id="{{ $admin->id }}"
											data-first_name="{{ $admin->first_name }}"
											data-last_name="{{ $admin->last_name }}"
											data-phone_number="{{ $admin->phone_number }}">
								Edit
							</button>

							<button type="button"
											class="btn-delete bg-red-600 text-white px-3 py-1.5 text-sm rounded"
											data-modal-open="#delete-modal"
											data-id="{{ $admin->id }}"
											data-firstname="{{ $admin->first_name }}"
											data-lastname="{{ $admin->last_name }}">
								Delete
							</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="py-6 text-center text-gray-500">No admins yet.</td>
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
		<form id="per-page-form" method="GET" action="{{ route('admin.index') }}"
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
			Showing {{ $admins->firstItem() ?? 0 }} – {{ $admins->lastItem() ?? 0 }} of {{ $admins->total() }}
		</div>

		<div id="pagination">
			{{ $admins->onEachSide(1)->links('vendor.pagination.always') }}
		</div>
	</div>
</div>

<x-ui.modal id="admin-modal"
            title="Add Admin"
            :form="['id'=>'admin-form','action'=>route('admin.store'),'method'=>'POST','submitText'=>'Submit']">
  <input type="hidden" name="_method" id="method-field" value="POST" data-clear-on-close>

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
    <label class="block text-sm mb-1">Phone Number</label>
    <input type="text" name="phone_number" id="phone_number"
           class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
           value="{{ old('phone_number') }}" placeholder="e.g., +639123456789, 09123456789" required>
    @error('phone_number') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <input type="hidden" name="face_descriptor_json" id="face_descriptor_json_admin" data-clear-on-close>

  <div class="mt-3 border-2 border-gray-300 rounded-xl p-3">
    <div class="flex items-center justify-between mb-2">
      <label class="block text-sm font-semibold">Face Capture (required)</label>
      <span class="text-xs text-gray-500">Ensure good lighting; remove masks/sunglasses.</span>
    </div>

    <div class="flex items-center gap-4">
      {{-- live preview --}}
      <video id="admin_cam" autoplay playsinline muted width="240" height="180" class="bg-black rounded"></video>
      {{-- snapshot preview --}}
      <canvas id="admin_snap" width="240" height="180" class="hidden rounded border"></canvas>

      <div class="flex flex-col gap-2">
        <button type="button" id="btn-capture-face-admin"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded disabled:opacity-50" disabled>
          Capture Face
        </button>
        <button type="button" id="btn-clear-face-admin"
          class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-1.5 rounded disabled:opacity-50" disabled>
          Clear
        </button>
        <span id="face-status-admin" class="text-xs text-gray-600 mt-1">No face captured yet.</span>
      </div>
    </div>
  </div>
  {{-- ===== /Face registration ===== --}}

  <x-ui.admin-auth class="pt-2" />
</x-ui.modal>

<x-ui.modal id="delete-modal"
            title="Delete Admin"
            :form="['id'=>'delete-form','action'=>'','method'=>'POST','spoof'=>'DELETE','submitText'=>'Delete']"
            size="max-w-[520px]">
  <input type="hidden" name="__action" value="delete" data-clear-on-close>
  <input type="hidden" name="__delete_id" id="__delete_id" data-clear-on-close>
  <input type="hidden" name="__delete_first_name" id="__delete_first_name" data-clear-on-close>
	<input type="hidden" name="__delete_last_name" id="__delete_last_name" data-clear-on-close>

  <p class="text-xl text-center font-semibold">
    Are you sure you want to delete
    <span class="font-black text-red-500" id="del-admin-name">this admin</span>?
  </p>

  <x-ui.admin-auth class="pt-2" />
</x-ui.modal>

<meta name="admin-update-url" content="{{ route('admin.update', ':id') }}">
<meta name="admin-delete-url" content="{{ route('admin.destroy', ':id') }}">
@endsection

<script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

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

  const updateTpl = document.querySelector('meta[name="admin-update-url"]').content;
  const deleteTpl = document.querySelector('meta[name="admin-delete-url"]').content;

  const adminModal 		= document.getElementById('admin-modal');
  const voterForm  		= document.getElementById('admin-form');
  const methodField   = document.getElementById('method-field');
  const modalTitleEl  = adminModal.querySelector('[data-modal-title]');
  const submitBtn     = adminModal.querySelector('[data-modal-submit]');

  const firstNameInp    = document.getElementById('first_name');
  const lastNameInp     = document.getElementById('last_name');
	const phoneNumberInp  = document.getElementById('phone_number');

  document.addEventListener('click', (e) => {
    if (e.target.closest('#btn-add')) {
      voterForm.action = @json(route('admin.store'));
      methodField.value   = 'POST';
      modalTitleEl.textContent = 'Add Admin';
      submitBtn.textContent = 'Submit';
      firstNameInp.value = '';
      lastNameInp.value  = '';
			phoneNumberInp.value = '';
      return;
    }

    const editBtn = e.target.closest('.btn-edit');
    if (editBtn) {
      const id  = editBtn.dataset.id;
      const url = updateTpl.replace(':id', id);

      voterForm.action = url;
      methodField.value   = 'PUT';
      modalTitleEl.textContent = 'Edit Admin';
      submitBtn.textContent = 'Update';

			firstNameInp.value = editBtn.dataset.first_name || '';
      lastNameInp.value  = editBtn.dataset.last_name || '';
			phoneNumberInp.value = editBtn.dataset.phone_number || '';
      return;
    }
  });

  const deleteModal = document.getElementById('delete-modal');
  const deleteForm  = document.getElementById('delete-form');
  const delFullNameSpan = document.getElementById('del-admin-name');
  const delIdHidden = document.getElementById('__delete_id');
  const delLastNameHidden = document.getElementById('__delete_last_name');
	const delFirstNameHidden = document.getElementById('__delete_first_name');

  document.addEventListener('click', (e) => {
    const delBtn = e.target.closest('.btn-delete');
    if (!delBtn) return;

    const id   = delBtn.dataset.id;
    const name = `${delBtn.dataset.firstname} ${delBtn.dataset.lastname}` || 'this admin';

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
    window.Modal.openById('admin-modal');
  @endif
</script>

<script>
(function(){
  function sayA(t){ const s=document.getElementById('face-status-admin'); if(s) s.textContent=t; console.log('[admin face]', t); }

  const adminModalId = 'admin-modal';
  const formId = 'admin-form';
  let A = {}, _astream=null, _amodels=false, _aModalObs=null;

  function ah(){
    A.modal = document.getElementById(adminModalId);
    A.cam   = document.getElementById('admin_cam');
    A.snap  = document.getElementById('admin_snap');
    A.cap   = document.getElementById('btn-capture-face-admin');
    A.clear = document.getElementById('btn-clear-face-admin');
    A.desc  = document.getElementById('face_descriptor_json_admin');
    A.form  = document.getElementById(formId);
  }

  function aResetUI(){
    ah();
    if(A.desc) A.desc.value = '';
    if(A.cam)  A.cam.classList.remove('hidden');
    if(A.snap) A.snap.classList.add('hidden');
    sayA('No face captured yet.');
  }

  function aStop(){
    if(_astream){ _astream.getTracks().forEach(t=>t.stop()); _astream=null; }
    ah();
    if(A.cap)   A.cap.disabled = true;
    if(A.clear) A.clear.disabled = true;
  }

  async function aLoadModels(){
    if(_amodels) return;
    try{
      await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
      await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
      await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
      _amodels = true;
    }catch(e){ console.warn('[admin face] model load:', e); }
  }

  async function aAttachStream(st, attempt=1){
    ah();
    if(!A.cam){
      if(attempt<=2){ await new Promise(r=>setTimeout(r,100)); return aAttachStream(st, attempt+1); }
      sayA('Video not ready. Please reopen the modal.'); return;
    }
    aStop();
    _astream = st;
    A.cam.srcObject = _astream;

    await new Promise(res => { A.cam.onloadedmetadata = () => res(); if (A.cam.readyState >= 1) res(); });
    try { A.cam.muted = true; A.cam.setAttribute('playsinline',''); await A.cam.play(); }
    catch(e){ A.cam.addEventListener('click', ()=> A.cam.play(), {once:true}); }

    if(A.cap)   A.cap.disabled   = false;
    if(A.clear) A.clear.disabled = false;
    sayA('Camera on. Click "Capture Face" when ready.');
  }

  async function aStartCam(){
    ah();
    try{
      await aLoadModels();

      let stream=null;
      try{
        const devs=await navigator.mediaDevices.enumerateDevices();
        const cams=devs.filter(d=>d.kind==='videoinput');
        if(cams[0]?.deviceId){
          stream = await navigator.mediaDevices.getUserMedia({
            video:{ deviceId:{exact:cams[0].deviceId}, width:{ideal:640}, height:{ideal:480} }, audio:false
          });
        }
      }catch(_){}
      if(!stream){
        try{
          stream = await navigator.mediaDevices.getUserMedia({
            video:{ facingMode:'user', width:{ideal:640}, height:{ideal:480} }, audio:false
          });
        }catch(_){}
      }
      if(!stream){
        stream = await navigator.mediaDevices.getUserMedia({ video:true, audio:false });
      }
      await aAttachStream(stream);
    }catch(e){
      console.error('[admin face] startCam:', e);
      sayA(`Camera error: ${e.name} — ${e.message}`);
    }
  }

  async function aCapture(){
    ah();
    const opts = new faceapi.TinyFaceDetectorOptions({ inputSize:224, scoreThreshold:0.5 });
    const det = await faceapi.detectSingleFace(A.cam, opts).withFaceLandmarks().withFaceDescriptor();
    if(!det){ sayA('No face detected. Center your face with good lighting and try again.'); return null; }
    return Array.from(det.descriptor);
  }

  function aStartModalObserver(){
    ah();
    if(!A.modal) return;
    if(_aModalObs){ _aModalObs.disconnect(); _aModalObs=null; }

    _aModalObs = new MutationObserver(() => {
      const visible = A.modal && A.modal.offsetParent !== null;
      if(!visible){ aStop(); }
    });
    _aModalObs.observe(A.modal, { attributes:true, attributeFilter:['style','class','aria-hidden'] });

    const rootObs = new MutationObserver(() => {
      const inDom = document.body.contains(A.modal);
      if(!inDom){ aStop(); rootObs.disconnect(); }
    });
    rootObs.observe(document.body, { childList:true, subtree:true });
  }

  document.addEventListener('click', (e)=>{
    if (e.target.closest('#btn-add') || e.target.closest('.btn-edit')) {
      if (!document.getElementById(adminModalId)) return; // safety
      aResetUI();
      sayA('Initializing camera…');
      setTimeout(()=>{ aStartCam(); aStartModalObserver(); }, 250);
    }
  });

  document.addEventListener('click', (e)=>{
    if (
      e.target.matches('[data-modal-close], .modal-close, [aria-label="Close"], button[title="Close"]') ||
      e.target.closest('[data-modal-close], .modal-close')
    ) { aStop(); }
  });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') aStop(); });
  window.addEventListener('beforeunload', aStop);

  document.addEventListener('click', (e)=>{
    if(e.target.id==='btn-capture-face-admin') (async()=>{
      ah();
      if(!_astream){ sayA('Camera not started yet.'); return; }

      if(A.snap && A.cam){
        const ctx=A.snap.getContext('2d');
        A.snap.classList.remove('hidden');
        ctx.drawImage(A.cam, 0, 0, A.snap.width, A.snap.height);
        A.cam.classList.add('hidden');
      }

      const vec = await aCapture();
      if(vec && A.desc){
        A.desc.value = JSON.stringify(vec);
        sayA('Face captured ✓');
      }
    })();

    if(e.target.id==='btn-clear-face-admin'){
      ah();
      if(A.desc) A.desc.value='';
      if(A.cam)  A.cam.classList.remove('hidden');
      if(A.snap) A.snap.classList.add('hidden');
      sayA('Cleared. Capture again if needed.');
    }
  });

  document.addEventListener('submit', (e)=>{
    if(e.target && e.target.id===formId){
      ah();
      if(!A.desc || !A.desc.value){
        e.preventDefault();
        sayA('Face capture is required. Please click "Capture Face".');
        return;
      }
      aStop();
    }
  });
})();
</script>
@endpush
