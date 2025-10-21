@php
	$title = 'Admin Face Verification | Voting System'
@endphp

@extends('layouts.auth')

@section('content')
<section class="flex items-center mx-auto gap-[120px]">
	<img src="{{ asset('logo.png') }}" alt="Logo" width="350" height="350" class="bg-white rounded-full p-0 m-0" />

	<div class="flex flex-col justify-center items-center">
		<h1 class="text-2xl uppercase text-black font-black text-center mb-8">PASEI SECURED ONLINE VOTING SYSTEM</h1>
		<div class="bg-white shadow-2xl p-[32px] rounded-4xl max-w-[500px] items-center flex flex-col">
			<form id="face-form" method="POST" action="{{ route('admin.face.verify') }}" class="space-y-4">
				@csrf
				<input type="hidden" name="face_descriptor_json" id="face_descriptor_json">
				<input type="hidden" name="next" value="{{ $nextUrl }}">

				<div class="space-y-2">
					<div class="flex items-center justify-between">
						<label class="font-semibold">Face Verification</label>
						<span class="text-xs text-gray-500">Good lighting • No mask/sunglasses</span>
					</div>

					<div class="flex items-center gap-4">
						<video id="cam" autoplay playsinline muted width="240" height="180" class="bg-black rounded"></video>
						<canvas id="snap" width="240" height="180" class="hidden rounded border"></canvas>

						<div class="flex flex-col gap-2">
							<button type="button" id="btn-capture" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded disabled:opacity-50" disabled>
								Capture
							</button>
							<button type="button" id="btn-retry" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-1.5 rounded disabled:opacity-50" disabled>
								Retry
							</button>
							<button type="submit" id="btn-proceed" class="bg-black hover:bg-gray-800 text-white px-4 py-1.5 rounded disabled:opacity-50" disabled>
								Proceed
							</button>

							<span id="status" class="text-xs text-gray-600 mt-1">Loading camera…</span>
						</div>
					</div>
				</div>

				@error('face')
					<p class="text-red-600 text-sm mt-2">{{ $message }}</p>
				@enderror
			</form>
		</div>
  </div>
</section>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js" defer></script>

  <script defer>
	async function waitForFaceApi(maxWaitMs = 8000){
		const start = Date.now();
		while (!window.faceapi) {
			await new Promise(r => setTimeout(r, 50));
			if (Date.now() - start > maxWaitMs) throw new Error('face-api not loaded in time');
		}
		return window.faceapi;
	}
  (function(){
    async function boot(){
      const S = {
        cam:   document.getElementById('cam'),
        snap:  document.getElementById('snap'),
        cap:   document.getElementById('btn-capture'),
        retry: document.getElementById('btn-retry'),
        go:    document.getElementById('btn-proceed'),
        desc:  document.getElementById('face_descriptor_json'),
        form:  document.getElementById('face-form'),
        status:document.getElementById('status'),
      };
      let stream = null, modelsLoaded = false;

      function say(t){ if(S.status) S.status.textContent = t; console.log('[face]', t); }
      function enable(el, v){ if(el) el.disabled = !v; }
      function showSnap(v){
        if(!S.snap || !S.cam) return;
        if(v){ S.snap.classList.remove('hidden'); S.cam.classList.add('hidden'); }
        else { S.snap.classList.add('hidden'); S.cam.classList.remove('hidden'); }
      }

      const faceapi = await waitForFaceApi();

      async function loadModels(){
        if(modelsLoaded) return;
        await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
        modelsLoaded = true;
      }

      async function startCamera(){
        try{
          await loadModels();
          stream = await navigator.mediaDevices.getUserMedia({
            video:{ facingMode:'user', width:{ideal:640}, height:{ideal:480} },
            audio:false
          });
          S.cam.srcObject = stream;
          await S.cam.play();
          enable(S.cap, true);
          enable(S.retry, true);
          say('Camera on. Align your face and click “Capture”.');
        }catch(e){
          console.error(e);
          say('Unable to access camera: ' + e.message);
        }
      }

      async function captureDescriptor(){
        const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });
        const det = await faceapi
          .detectSingleFace(S.cam, opts)
          .withFaceLandmarks()
          .withFaceDescriptor();
        if(!det) return null;
        return Array.from(det.descriptor);
      }

      S.cap?.addEventListener('click', async ()=>{
        if(!stream){ say('Camera not ready.'); return; }

        const ctx = S.snap.getContext('2d');
        ctx.drawImage(S.cam, 0, 0, S.snap.width, S.snap.height);
        showSnap(true);

        say('Analyzing face…');
        const vec = await captureDescriptor();
        if(!vec){ say('No face detected. Keep your face centered and try again.'); showSnap(false); return; }

        S.desc.value = JSON.stringify(vec);
        say('Face captured ✓ You can proceed.');
        enable(S.go, true);
      });

      S.retry?.addEventListener('click', ()=>{
        S.desc.value = '';
        showSnap(false);
        say('Try capturing again.');
        enable(S.go, false);
      });

      function stop(){
        if(stream){ stream.getTracks().forEach(t=>t.stop()); stream = null; }
      }
      window.addEventListener('beforeunload', stop);
      document.addEventListener('visibilitychange', ()=>{ if(document.hidden) stop(); });

      S.form?.addEventListener('submit', (e)=>{
        if(!S.desc.value){
          e.preventDefault();
          say('Please capture your face first.');
        }
      });

      startCamera();
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', boot, { once:true });
    } else {
      boot();
    }

  })();
  </script>
@endpush

