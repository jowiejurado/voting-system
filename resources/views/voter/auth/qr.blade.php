@php $title = 'Voter - QR Code Verification | Voting System'; @endphp
@extends('layouts.voter-auth')
@section('content')
	<div class="flex items-center mx-auto gap-[120px]">
		<img src="{{ asset('logo.png') }}" alt="Logo" width="350" height="350" class="bg-white rounded-full p-0 m-0" />
		<div class="flex flex-col justify-center items-center">
			<h1 class="text-2xl uppercase text-black font-black text-center mb-8">PASEI SECURED ONLINE VOTING SYSTEM</h1>
			<div class="bg-white shadow-2xl p-[32px] rounded-4xl max-w-[500px] items-center flex flex-col">
				<div class="flex flex-col items-center gap-[24px]">
					<h4 class="text-lg text-black font-bold">3rd Step Verification - QR Code Verification</h4>
					<label class="label">Click the button to open camera</label>
					<button
						type="button"
						class="inline-block py-4 px-8 rounded-3xl border-none bg-black text-white cursor-pointer font-semibold"
						data-modal-open="#qr-modal">
						Camera
					</button>
				</form>
			</div>
		</div>
	</div>

	<x-ui.modal id="qr-modal" title="Scan the QR Code" size="max-w-[720px]">
		<div class="space-y-4">
			{{-- Camera window --}}
			<div class="relative rounded-xl overflow-hidden border border-gray-200 shadow-inner">
				<div class="aspect-[4/3] bg-black grid place-items-center">
					<video id="qr-video"
								autoplay
								playsinline
								muted
								class="w-full h-full object-cover"></video>
				</div>
			</div>

			{{-- Controls --}}
			<div class="flex items-center justify-end gap-2">
				<button id="qr-switch"
								type="button"
								class="hidden px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300">
					Switch camera
				</button>
				<button id="qr-stop"
								type="button"
								class="hidden px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300"
								data-modal-close>
					Stop
				</button>
			</div>

			{{-- Error + fallback --}}
			<p id="qr-error" class="text-sm text-red-600"></p>
			<div id="qr-fallback" class="hidden">
				<p class="text-sm text-gray-600">
					If your browser blocks the camera, upload a clear photo/screenshot of the QR instead.
				</p>
				<input type="file"
							name="qr_image"
							accept="image/*"
							capture="environment"
							class="mt-2 block w-full text-sm text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800" />
			</div>
		</div>
	</x-ui.modal>
@endsection

@push('scripts')
<script>
(function(){
  const modal     = document.getElementById('qr-modal');
  const video     = document.getElementById('qr-video');
  const errEl     = document.getElementById('qr-error');
  const switchBtn = document.getElementById('qr-switch');
  const stopBtn   = document.getElementById('qr-stop');
  const fallback  = document.getElementById('qr-fallback');

  let stream = null;
  let devices = [];
  let currentIndex = 0;

  function showError(msg){
    errEl.textContent = msg || '';
  }

  async function listCameras(){
    try {
      const all = await navigator.mediaDevices.enumerateDevices();
      devices = all.filter(d => d.kind === 'videoinput');
    } catch(e){ /* ignore */ }
  }

  async function startCamera(deviceId = null){
    showError('');
    fallback.classList.add('hidden');

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      fallback.classList.remove('hidden');
      showError('Camera API not supported on this browser.');
      return;
    }

    try {
      const constraints = deviceId
        ? { video: { deviceId: { exact: deviceId } }, audio: false }
        : { video: { facingMode: { ideal: 'environment' } }, audio: false };

      stream = await navigator.mediaDevices.getUserMedia(constraints);
      video.srcObject = stream;
      await video.play();

      // After permission, device labels become available
      await listCameras();

      if (deviceId) {
        currentIndex = devices.findIndex(d => d.deviceId === deviceId);
      } else {
        const label = stream.getVideoTracks()[0]?.label || '';
        const idx = devices.findIndex(d => d.label === label);
        if (idx >= 0) currentIndex = idx;
      }

      switchBtn.classList.toggle('hidden', devices.length < 2);
      // stopBtn.classList.remove('hidden');
    } catch (err) {
      stopCamera();
      fallback.classList.remove('hidden');
      switchBtn.classList.add('hidden');
      showError(err && err.message ? err.message : 'Unable to access camera.');
    }
  }

  function stopCamera(){
    if (stream) {
      stream.getTracks().forEach(t => t.stop());
      stream = null;
    }
    video.srcObject = null;
    // stopBtn.classList.add('hidden');
  }

  async function switchCamera(){
    if (devices.length < 2) return;
    currentIndex = (currentIndex + 1) % devices.length;
    const devId = devices[currentIndex].deviceId;
    stopCamera();
    startCamera(devId);
  }

  switchBtn.addEventListener('click', switchCamera);
  stopBtn.addEventListener('click', stopCamera);
  window.addEventListener('beforeunload', stopCamera);

  // Auto start/stop when the modal opens/closes.
  // Uses MutationObserver since your modal toggles `hidden` on the root.
  const observer = new MutationObserver(() => {
    const isHidden = modal.classList.contains('hidden');
    if (!isHidden) {
      startCamera();
    } else {
      stopCamera();
    }
  });
  observer.observe(modal, { attributes: true, attributeFilter: ['class'] });

  // Also start immediately if the page loads with modal already open (edge case)
  if (!modal.classList.contains('hidden')) startCamera();

})();
</script>
@endpush
