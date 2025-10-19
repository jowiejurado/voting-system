@php($title = 'Voters | Voting System')

@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 px-10 pt-5">
  <div class="flex flex-col gap-y-5">
    <h1 class="text-2xl font-black text-[#0b252a]">Voters</h1>
    <div class="flex items-center justify-between">
      <button type="button" id="btn-add"
              class="bg-[#545454] hover:bg-[#686868] cursor-pointer px-6 py-2 rounded-full text-white"
              data-modal-open="#voter-modal">
        Add Voter
      </button>

      <form id="search-form" method="GET" action="{{ route('admin.voters.index') }}"
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
    <table class="table-fixed w-full" id="voters-table">
      <thead>
        <tr class="border-b-2 border-gray-400">
          <th class="py-3 text-center">First Name</th>
          <th class="py-3 text-center">Last Name</th>
          <th class="py-3 text-center">Member ID</th>
          <th class="py-3 text-center">Organization</th>
          <th class="w-56 py-3 text-center">Tools</th>
        </tr>
      </thead>
      <tbody class="text-center">
        @forelse($voters as $voter)
          <tr class="border-b-2 border-gray-400 last:border-b-0">
            <td class="py-3 px-6">{{ $voter->first_name }}</td>
            <td class="py-3 px-6">{{ $voter->last_name }}</td>
            <td class="py-3 px-6">{{ $voter->member_id }}</td>
            <td class="py-3 text-center">{{ $voter->organization_name }}</td>
            <td class="py-3 text-center">
              <button type="button"
                      class="btn-edit bg-green-600 text-white px-3 py-[6px] text-sm rounded"
                      data-modal-open="#voter-modal"
                      data-id="{{ $voter->id }}"
                      data-first_name="{{ $voter->first_name }}"
                      data-last_name="{{ $voter->last_name }}"
                      data-member_id="{{ $voter->member_id }}"
                      data-phone_number="{{ $voter->phone_number }}"
                      data-organization_name="{{ $voter->organization_name }}">
                Edit
              </button>

              <button type="button"
                      class="btn-delete bg-red-600 text-white px-3 py-1.5 text-sm rounded"
                      data-modal-open="#delete-modal"
                      data-id="{{ $voter->id }}"
                      data-firstname="{{ $voter->first_name }}"
                      data-lastname="{{ $voter->last_name }}">
                Delete
              </button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="py-6 text-center text-gray-500">No voters yet.</td>
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
    <form id="per-page-form" method="GET" action="{{ route('admin.voters.index') }}"
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
      Showing {{ $voters->firstItem() ?? 0 }} – {{ $voters->lastItem() ?? 0 }} of {{ $voters->total() }}
    </div>

    <div id="pagination">
      {{ $voters->onEachSide(1)->links('vendor.pagination.always') }}
    </div>
  </div>
</div>

{{-- ================== VOTER MODAL ================== --}}
<x-ui.modal id="voter-modal"
            title="Add Voter"
            :form="['id'=>'voter-form','action'=>route('admin.voters.store'),'method'=>'POST','submitText'=>'Submit']">
  <input type="hidden" name="_method" id="method-field" value="POST" data-clear-on-close>
  <input type="hidden" name="face_descriptor_json" id="face_descriptor_json" data-clear-on-close>

  {{-- SCROLLABLE BODY --}}
  <div class="max-h-[75vh] md:max-h-[70vh] overflow-y-auto pr-2 space-y-4">
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

    <div>
      <label class="block text-sm mb-1">Organization Name</label>
      <input type="text" name="organization_name" id="organization_name"
             class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
             value="{{ old('organization_name') }}" placeholder="e.g., IT, Marketing" required>
      @error('organization_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Face capture UI (REQUIRED) --}}
    <div class="mt-3 border-2 border-gray-300 rounded-xl p-3">
      <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-semibold">Face Capture (required)</label>
        <span class="text-xs text-gray-500">Ensure good lighting; remove masks/sunglasses.</span>
      </div>

      <div class="flex items-center gap-4">
        {{-- live preview --}}
        <video id="voter_cam" autoplay playsinline muted width="240" height="180" class="bg-black rounded"></video>
        {{-- snapshot preview --}}
        <canvas id="voter_snap" width="240" height="180" class="hidden rounded border"></canvas>

        <div class="flex flex-col gap-2">
          <button type="button" id="btn-capture-face"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded disabled:opacity-50" disabled>
            Capture Face
          </button>
          <button type="button" id="btn-clear-face"
            class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-1.5 rounded disabled:opacity-50" disabled>
            Clear
          </button>
          <span id="face-status" class="text-xs text-gray-600 mt-1">No face captured yet.</span>
        </div>
      </div>
    </div>

    <x-ui.admin-auth class="pt-2" />
  </div>
  {{-- /SCROLLABLE BODY --}}
</x-ui.modal>

{{-- ================== DELETE MODAL ================== --}}
<x-ui.modal id="delete-modal"
            title="Delete Voter"
            :form="['id'=>'delete-form','action'=>'','method'=>'POST','spoof'=>'DELETE','submitText'=>'Delete']"
            size="max-w-[520px]">
  <input type="hidden" name="__action" value="delete" data-clear-on-close>
  <input type="hidden" name="__delete_id" id="__delete_id" data-clear-on-close>
  <input type="hidden" name="__delete_first_name" id="__delete_first_name" data-clear-on-close>
  <input type="hidden" name="__delete_last_name" id="__delete_last_name" data-clear-on-close>

  <div class="max-h-[70vh] overflow-y-auto pr-2 space-y-4">
    <p class="text-xl text-center font-semibold">
      Are you sure you want to delete
      <span class="font-black text-red-500" id="del-voter-name">this voter</span>?
    </p>
    <x-ui.admin-auth class="pt-2" />
  </div>
</x-ui.modal>

{{-- Meta routes for JS (avoid Blade in <script>) --}}
<meta name="voter-store-url" content="{{ route('admin.voters.store') }}">
<meta name="voter-update-url" content="{{ route('admin.voters.update', ':id') }}">
<meta name="voter-delete-url" content="{{ route('admin.voters.destroy', ':id') }}">
@endsection

{{-- Face API --}}
<script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

@verbatim
<script>
  // ========== helper
  function say(t){ const s=document.getElementById('face-status'); if(s) s.textContent=t; console.log('[face]', t); }

  // ========== refs/state
  let refs = {};
  function hydrate(){
    refs.modal = document.getElementById('voter-modal');
    refs.cam   = document.getElementById('voter_cam');
    refs.snap  = document.getElementById('voter_snap');
    refs.cap   = document.getElementById('btn-capture-face');
    refs.clear = document.getElementById('btn-clear-face');
    refs.desc  = document.getElementById('face_descriptor_json');
    refs.form  = document.getElementById('voter-form');
  }

  let _stream = null, _modelsLoaded = false, _modalObserver = null;

  function resetFaceUI(){
    hydrate();
    if(refs.desc) refs.desc.value = '';
    if(refs.cam)  refs.cam.classList.remove('hidden');
    if(refs.snap) refs.snap.classList.add('hidden');
    say('No face captured yet.');
  }

  function stopCamera(){
    if (_stream) { _stream.getTracks().forEach(t => t.stop()); _stream = null; }
    if (refs.cap)   refs.cap.disabled   = true;
    if (refs.clear) refs.clear.disabled = true;
  }

  function startModalObserver(){
    hydrate();
    if (!refs.modal) return;
    if (_modalObserver) { _modalObserver.disconnect(); _modalObserver = null; }

    _modalObserver = new MutationObserver(() => {
      const isVisible = refs.modal && refs.modal.offsetParent !== null;
      if (!isVisible) { stopCamera(); }
    });
    _modalObserver.observe(refs.modal, { attributes: true, attributeFilter: ['style','class','aria-hidden'] });

    // Also stop if modal is removed from DOM
    const rootObserver = new MutationObserver(() => {
      const stillInDom = document.body.contains(refs.modal);
      if (!stillInDom) { stopCamera(); rootObserver.disconnect(); }
    });
    rootObserver.observe(document.body, { childList:true, subtree:true });
  }

  async function attachStream(st, attempt=1){
    hydrate();
    if (!refs.cam) {
      if (attempt <= 2) { await new Promise(r => setTimeout(r, 100)); return attachStream(st, attempt+1); }
      say('Video not ready. Please reopen the modal.');
      return;
    }
    stopCamera();
    _stream = st;
    refs.cam.srcObject = _stream;

    await new Promise(res => { refs.cam.onloadedmetadata = () => res(); if (refs.cam.readyState >= 1) res(); });
    try { refs.cam.muted = true; refs.cam.setAttribute('playsinline',''); await refs.cam.play(); }
    catch(e) { refs.cam.addEventListener('click', ()=> refs.cam.play(), { once:true }); }

    if (refs.cap)   refs.cap.disabled   = false;
    if (refs.clear) refs.clear.disabled = false;
    say('Camera on. Click "Capture Face" when ready.');
  }

  async function loadModelsOnce(){
    if(_modelsLoaded) return;
    try{
      await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
      await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
      await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
      _modelsLoaded = true;
    }catch(e){
      console.warn('Model load warning:', e);
    }
  }

  async function startCamera(){
    hydrate();
    try{
      await loadModelsOnce();

      let stream = null;
      try{
        const devs = await navigator.mediaDevices.enumerateDevices();
        const cams = devs.filter(d => d.kind === 'videoinput');
        if (cams[0]?.deviceId) {
          stream = await navigator.mediaDevices.getUserMedia({
            video: { deviceId:{ exact:cams[0].deviceId }, width:{ideal:640}, height:{ideal:480} },
            audio: false
          });
        }
      }catch(_){}

      if(!stream){
        try{
          stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode:'user', width:{ideal:640}, height:{ideal:480} },
            audio:false
          });
        }catch(_){}
      }
      if(!stream){
        stream = await navigator.mediaDevices.getUserMedia({ video:true, audio:false });
      }

      await attachStream(stream);
    }catch(e){
      console.error('startCamera error:', e);
      say(`Camera error: ${e.name} — ${e.message}`);
    }
  }

  async function captureDescriptor(){
    hydrate();
    const opts = new faceapi.TinyFaceDetectorOptions({ inputSize:224, scoreThreshold:0.5 });
    const det = await faceapi.detectSingleFace(refs.cam, opts).withFaceLandmarks().withFaceDescriptor();
    if(!det){ say('No face detected. Center your face with good lighting and try again.'); return null; }
    return Array.from(det.descriptor);
  }

  // Open modal → auto camera + observer
  document.addEventListener('click', (e) => {
    if (e.target.closest('#btn-add') || e.target.closest('.btn-edit')) {
      resetFaceUI();
      say('Initializing camera…');
      setTimeout(() => { startCamera(); startModalObserver(); }, 250);
    }
  });

  // Close modal → stop camera (X button, backdrop, etc.)
  document.addEventListener('click', (e) => {
    if (
      e.target.matches('[data-modal-close], .modal-close, [aria-label="Close"], button[title="Close"]') ||
      e.target.closest('[data-modal-close], .modal-close')
    ) {
      stopCamera();
    }
  });
  // ESC → close → stop camera
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') stopCamera(); });

  // Buttons
  document.addEventListener('click', (e) => {
    if (e.target.id === 'btn-capture-face') (async () => {
      hydrate();
      if(!_stream){ say('Camera not started yet.'); return; }

      // snapshot
      if(refs.snap && refs.cam){
        const ctx = refs.snap.getContext('2d');
        refs.snap.classList.remove('hidden');
        ctx.drawImage(refs.cam, 0, 0, refs.snap.width, refs.snap.height);
        refs.cam.classList.add('hidden');
      }

      const vec = await captureDescriptor();
      if (vec && refs.desc) {
        refs.desc.value = JSON.stringify(vec);
        say('Face captured ✓');
      }
    })();

    if (e.target.id === 'btn-clear-face') {
      hydrate();
      if (refs.desc) refs.desc.value = '';
      if(refs.cam)  refs.cam.classList.remove('hidden');
      if(refs.snap) refs.snap.classList.add('hidden');
      say('Cleared. Capture again if needed.');
    }
  });

  // Require face before submit; stop camera on successful submit
  document.addEventListener('submit', (e) => {
    hydrate();
    if (e.target && e.target.id === 'voter-form') {
      if (!refs.desc || !refs.desc.value) {
        e.preventDefault();
        say('Face capture is required. Please click "Capture Face".');
        return;
      }
      stopCamera(); // stop stream right before navigation
    }
  });

  // Safety
  window.addEventListener('beforeunload', stopCamera);

  // ===== Table/search utilities
  function showTableLoading(){ const el=document.getElementById('table-loading'); if(el) el.classList.remove('hidden'); }
  window.addEventListener('pageshow', (e)=>{ if(e.persisted){ const el=document.getElementById('table-loading'); if(el) el.classList.add('hidden'); } });

  (function(){
    const input=document.getElementById('search'), form=document.getElementById('search-form');
    if(!input||!form) return; let t;
    input.addEventListener('input', ()=>{ clearTimeout(t); t=setTimeout(()=>{ showTableLoading(); form.submit(); }, 350); });
    input.focus(); const len=input.value.length; try{ input.setSelectionRange(len,len); }catch(e){}
  })();

  (function(){
    const perPageForm=document.getElementById('per-page-form'); if(!perPageForm) return;
    const select=perPageForm.querySelector('select[name="per_page"]'); if(!select) return;
    select.addEventListener('change', ()=>{ showTableLoading(); });
  })();

  (function(){
    const pager=document.getElementById('pagination'); if(!pager) return;
    pager.addEventListener('click', (e)=>{ const a=e.target.closest('a'); if(!a) return; showTableLoading(); });
  })();

  // ===== Modal form wiring (add/edit/delete)
  const updateTpl = document.querySelector('meta[name="voter-update-url"]')?.content;
  const deleteTpl = document.querySelector('meta[name="voter-delete-url"]')?.content;
  const storeUrl  = document.querySelector('meta[name="voter-store-url"]')?.content;

  const voterModal   = document.getElementById('voter-modal');
  const voterForm    = document.getElementById('voter-form');
  const methodField  = document.getElementById('method-field');
  const modalTitleEl = voterModal?.querySelector('[data-modal-title]');
  const submitBtn    = voterModal?.querySelector('[data-modal-submit]');

  const firstNameInp    = document.getElementById('first_name');
  const lastNameInp     = document.getElementById('last_name');
  const orgNameInp      = document.getElementById('organization_name');
  const memberIdInp     = document.getElementById('member_id');
  const phoneNumberInp  = document.getElementById('phone_number');

  document.addEventListener('click', (e)=>{
    if(e.target.closest('#btn-add')){
      if(voterForm && storeUrl) voterForm.action = storeUrl;
      if(methodField)  methodField.value   = 'POST';
      if(modalTitleEl) modalTitleEl.textContent = 'Add Voter';
      if(submitBtn)    submitBtn.textContent    = 'Submit';
      if(firstNameInp) firstNameInp.value = '';
      if(lastNameInp)  lastNameInp.value  = '';
      if(orgNameInp)   orgNameInp.value   = '';
      if(memberIdInp)  memberIdInp.value  = '';
      if(phoneNumberInp) phoneNumberInp.value = '';
      return;
    }

    const editBtn = e.target.closest('.btn-edit');
    if(editBtn){
      const id  = editBtn.dataset.id;
      const url = (updateTpl || '').replace(':id', id);
      if(voterForm && url) voterForm.action = url;
      if(methodField)  methodField.value = 'PUT';
      if(modalTitleEl) modalTitleEl.textContent = 'Edit Voter';
      if(submitBtn)    submitBtn.textContent    = 'Update';

      if(firstNameInp)   firstNameInp.value   = editBtn.dataset.first_name || '';
      if(lastNameInp)    lastNameInp.value    = editBtn.dataset.last_name  || '';
      if(orgNameInp)     orgNameInp.value     = editBtn.dataset.organization_name || '';
      if(memberIdInp)    memberIdInp.value    = editBtn.dataset.member_id  || '';
      if(phoneNumberInp) phoneNumberInp.value = editBtn.dataset.phone_number || '';
      return;
    }
  });

  const deleteForm  = document.getElementById('delete-form');
  const delFullNameSpan = document.getElementById('del-voter-name');
  const delIdHidden     = document.getElementById('__delete_id');
  const delLastNameHidden = document.getElementById('__delete_last_name');
  const delFirstNameHidden = document.getElementById('__delete_first_name');

  document.addEventListener('click', (e)=>{
    const delBtn = e.target.closest('.btn-delete'); if(!delBtn) return;
    const id   = delBtn.dataset.id;
    const name = `${delBtn.dataset.firstname} ${delBtn.dataset.lastname}` || 'this voter';
    if(deleteForm && deleteTpl) deleteForm.action = deleteTpl.replace(':id', id);
    if(delIdHidden)        delIdHidden.value = id;
    if(delLastNameHidden)  delLastNameHidden.value = delBtn.dataset.lastname;
    if(delFirstNameHidden) delFirstNameHidden.value = delBtn.dataset.firstname;
    if(delFullNameSpan)    delFullNameSpan.textContent = name;
  });
</script>
@endverbatim
