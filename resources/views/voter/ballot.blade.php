@php($title = 'Ballot | Voting System')

@extends('layouts.voter-app')

@section('content')
<div class="flex flex-col items-center justify-center gap-y-6 m-7">

  {{-- Title --}}
  <h1 class="bg-black text-white py-4 px-12 text-4xl font-black rounded-full">
    {{ $election->title ?? 'Election' }}
  </h1>

  {{-- Outer Card --}}
  <div class="flex flex-col gap-10 bg-[#f3f3f3] rounded-4xl p-10 min-w-[1280px] min-h-[550px]">

    {{-- Header: position name + Skip (or Receipt title) --}}
    <div class="flex items-center justify-between">
      <span id="position-pill"
            class="px-6 py-1.5 text-2xl bg-black rounded-full font-black text-white">
        <!-- filled by JS -->
      </span>

      <div class="flex gap-3">
        <button type="button" id="btn-prev"
                class="cursor-pointer bg-gray-700 hover:bg-gray-700/80 px-6 text-white py-1.5 rounded-full text-2xl font-black hidden">
          Previous
        </button>

        <button type="button" id="btn-skip"
                class="cursor-pointer bg-black hover:bg-black/80 px-6 text-white py-1.5 rounded-full text-2xl font-black">
          Skip
        </button>
      </div>
    </div>

    {{-- Candidate Grid / Receipt Area --}}
    <div id="content-area" class="flex flex-wrap gap-x-10 gap-y-10 items-start justify-center">
      {{-- filled by JS --}}
    </div>

    {{-- Footer Nav --}}
    <div id="footer-nav" class="flex items-center justify-end">
      <button type="button" id="btn-next"
              class="cursor-pointer bg-green-600 hover:bg-green-600/80 disabled:bg-gray-400 disabled:cursor-not-allowed px-6 text-white py-1.5 rounded-full text-2xl font-black">
        Next
      </button>
    </div>
  </div>

  {{-- Submit form (hidden) --}}
  <form id="ballot-form" method="POST" action="{{ route('voter.ballot.submit') }}" class="hidden">
    @csrf
    <input type="hidden" name="election_id" value="{{ $election->id }}">
    {{-- JS will append inputs like: positions[<position_id>][] = <candidate_id> --}}
  </form>
</div>

{{-- Skip Warning Modal (centered with warning icon) --}}
<div id="skip-modal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50">
  <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center">
    <div class="mx-auto mb-4 flex items-center justify-center">
      <div class="w-25 h-25 rounded-full bg-red-100 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 text-red-600" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l6.518 11.597c.73 1.298-.184 2.904-1.742 2.904H3.48c-1.558 0-2.472-1.606-1.742-2.904L8.257 3.1zM11 14a1 1 0 10-2 0 1 1 0 002 0zm-1-2a1 1 0 01-1-1V8a1 1 0 112 0v3a1 1 0 01-1 1z" clip-rule="evenodd"/>
        </svg>
      </div>
    </div>
    <h3 class="text-2xl font-black mb-2">Are you sure you want to skip this position?</h3>
    <div class="mt-6 flex justify-center gap-3">
      <button type="button" id="skip-cancel"
              class="px-5 py-2 rounded-full bg-red-500 text-white font-semibold">
        Cancel
      </button>
      <button type="button" id="skip-confirm"
              class="px-5 py-2 rounded-full bg-green-500 text-white font-black">
        Yes
      </button>
    </div>
  </div>
</div>

{{-- Retake / Reject Warning Modal --}}
<div id="retake-modal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50">
  <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center">
    <div class="mx-auto mb-4 flex items-center justify-center">
      <div class="w-25 h-25 rounded-full bg-red-100 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 text-red-600" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l6.518 11.597c.73 1.298-.184 2.904-1.742 2.904H3.48c-1.558 0-2.472-1.606-1.742-2.904L8.257 3.1zM11 14a1 1 0 10-2 0 1 1 0 002 0zm-1-2a1 1 0 01-1-1V8a1 1 0 112 0v3a1 1 0 01-1 1z" clip-rule="evenodd"/>
        </svg>
      </div>
    </div>
    <h3 class="text-2xl font-black mb-1">Are you sure to retake your voting?</h3>
    <div class="mt-6 flex justify-center gap-3">
      <button type="button" id="retake-cancel"
              class="px-5 py-2 rounded-full bg-red-500 text-white font-semibold">
        Cancel
      </button>
      <button type="button" id="retake-confirm"
              class="px-5 py-2 rounded-full bg-green-500 text-white font-black">
        Yes
      </button>
    </div>
  </div>
</div>

{{-- Thank You Modal --}}
<div id="thanks-modal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50">
  <div class="bg-white rounded-3xl p-8 max-w-xl w-full text-center">
    <div class="mx-auto mb-4 flex items-center justify-center">
      <div class="w-25 h-25 rounded-full bg-green-100 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 text-green-600" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.43 7.43a1 1 0 01-1.414 0L3.293 9.566a1 1 0 011.414-1.414l3.004 3.004 6.723-6.723a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
      </div>
    </div>
    <h3 class="text-2xl font-black">VOTES ACCEPTED, THANK YOU FOR VOTING IN THIS ELECTION!</h3>
    <p class="text-gray-700 mt-2">You’ll be redirected shortly…</p>
  </div>
</div>

{{-- Data payloads from PHP to JS --}}
<script>
  window.__BALLOT__ = {
    electionId: @json($election->id),
    positions:  @json($positionsPayload),
		loginUrl:   @json(route('voter.login'))
  };
</script>

{{-- Stepper Logic (Vanilla JS) --}}
<script>
(function(){
  const data = window.__BALLOT__;
  const positions = data.positions || [];
  const state = {
    step: 0,                 // 0..positions.length (receipt at == length)
    chosen: Object.create(null),  // { [positionId]: number[] }
  };

  // DOM refs
  const content = document.getElementById('content-area');
  const pill = document.getElementById('position-pill');
  const btnNext = document.getElementById('btn-next');
  const btnPrev = document.getElementById('btn-prev');
  const btnSkip = document.getElementById('btn-skip');

  const skipModal = document.getElementById('skip-modal');
  const skipCancel = document.getElementById('skip-cancel');
  const skipConfirm = document.getElementById('skip-confirm');

	const retakeModal = document.getElementById('retake-modal');
	const retakeCancel = document.getElementById('retake-cancel');
	const retakeConfirm = document.getElementById('retake-confirm');

  const form = document.getElementById('ballot-form');

  // helpers
  const current = () => positions[state.step];
  const getChosen = (pid) => state.chosen[pid] || [];
  const setChosen = (pid, arr) => { state.chosen[pid] = arr; };
  const hasSelection = (pos) => (getChosen(pos.id).length > 0);

  function render() {
		content.innerHTML = '';

		const atReceipt = state.step === positions.length;
		pill.textContent = atReceipt ? 'Vote Receipt' : (current()?.name || '');

		// hide/show top controls
		btnSkip.classList.toggle('hidden', atReceipt);
		btnPrev.classList.toggle('hidden', state.step === 0);

		// NEW: hide footer nav entirely on receipt so there's only one Submit button
		const footer = document.getElementById('footer-nav');
		if (footer) footer.classList.toggle('hidden', atReceipt);

		// keep the Next label on position steps
		btnNext.textContent = 'Next';

		if (atReceipt) {
			renderReceipt();
			return;
		}

		renderPosition(current());
		btnNext.disabled = !hasSelection(current());
	}

  function renderPosition(pos){
    // note
    const note = document.createElement('p');
    note.className = 'text-lg text-gray-700 w-full text-center';
    note.innerHTML = `Select up to <b>${pos.max}</b> candidate(s).`;
    content.appendChild(note);

    // grid
    const grid = document.createElement('div');
    grid.className = 'flex flex-wrap gap-x-10 gap-y-10 items-start justify-center w-full';
    pos.candidates.forEach(c => grid.appendChild(cardForCandidate(pos, c)));
    content.appendChild(grid);
  }

  function cardForCandidate(pos, cand){
    const pid = pos.id;
    const selected = getChosen(pid);
    const isChecked = selected.includes(cand.id);

    const wrap = document.createElement('div');
    wrap.className = 'basis-[calc(33.333%-2.5rem)] flex flex-col items-center gap-6';

    // avatar
    const avatar = document.createElement('div');
    avatar.className = 'h-25 w-25 rounded-full bg-transparent flex items-center border-6 border-black';
    avatar.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" class="h-25 w-25" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/>
      </svg>`;
    wrap.appendChild(avatar);

    // text
    const txt = document.createElement('div');
    txt.className = 'text-center';
    txt.innerHTML = `
      <p class="font-black text-xl">${escapeHtml(cand.name)}</p>
      <p class="font-bold text-md">${escapeHtml(pos.name)}</p>
      <p class="font-medium text-xs">${escapeHtml(cand.org || '')}</p>
    `;
    wrap.appendChild(txt);

    // Vote toggle button (label always "Vote", color toggles)
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = voteBtnClass(isChecked); // green when selected, black otherwise
    btn.textContent = 'Vote';
    btn.setAttribute('aria-pressed', isChecked ? 'true' : 'false');

    btn.addEventListener('click', () => toggleSelection(pos, cand, btn));
    wrap.appendChild(btn);

    return wrap;
  }

	function voteBtnClass(selected){
		return selected
			? 'bg-green-600 hover:bg-green-600/80 cursor-pointer px-6 text-white py-2 rounded-full text-2xl font-black'
			: 'bg-black hover:bg-black/80 cursor-pointer px-6 text-white py-2 rounded-full text-2xl font-black';
	}

  function toggleSelection(pos, cand, btnEl){
    const pid = pos.id;
    const arr = getChosen(pid).slice();
    const idx = arr.indexOf(cand.id);

    if (idx >= 0) {
      // unselect
      arr.splice(idx, 1);
      setChosen(pid, arr);
      btnEl.className = voteBtnClass(false);
      btnEl.setAttribute('aria-pressed', 'false');
    } else {
      // add but enforce max
      if (arr.length >= pos.max) {
        btnEl.classList.add('ring-4','ring-red-400');
        setTimeout(() => btnEl.classList.remove('ring-4','ring-red-400'), 300);
        return;
      }
      arr.push(cand.id);
      setChosen(pid, arr);
      btnEl.className = voteBtnClass(true);
      btnEl.setAttribute('aria-pressed', 'true');
    }

    // Enable/disable Next + tip visibility after any change
    if (state.step < positions.length) {
      const atLeastOne = hasSelection(pos);
      btnNext.disabled = !atLeastOne;
      const tip = document.getElementById('tip-select');
      if (tip) tip.style.display = atLeastOne ? 'none' : 'block';
    }
  }

  function renderReceipt(){
		const wrap = document.createElement('div');
		wrap.className = 'w-full';

		// Title
		const h = document.createElement('h2');
		h.className = 'text-xl font-black mb-4 text-center';
		h.textContent = 'Review your selections';
		wrap.appendChild(h);

		// Compact list (2-column rows)
		const list = document.createElement('div');
		list.className = 'bg-white rounded-3xl p-4 border divide-y';
		positions.forEach(pos => {
			const row = document.createElement('div');
			row.className = 'py-3 grid grid-cols-12 gap-4 items-start';

			const left = document.createElement('div');
			left.className = 'col-span-4 font-semibold';
			left.textContent = pos.name;

			const right = document.createElement('div');
			right.className = 'col-span-8 text-sm';
			const chosen = getChosen(pos.id);

			if (chosen.length) {
				const names = chosen.map(cid => {
					const c = pos.candidates.find(x => x.id === cid);
					return c ? `${c.name}${c.org ? ' ('+c.org+')' : ''}` : `#${cid}`;
				}).join(', ');
				right.textContent = names;
			} else {
				right.innerHTML = '<span class="inline-block px-2 py-0.5 rounded-full bg-gray-100 border text-gray-700">Skipped</span>';
			}

			row.appendChild(left);
			row.appendChild(right);
			list.appendChild(row);
		});
		wrap.appendChild(list);

		// Actions (only one Submit + a Reject)
		const actions = document.createElement('div');
		actions.className = 'flex items-center justify-end gap-3 mt-4';
		actions.innerHTML = `
			<button type="button" id="btn-retake"
							class="px-5 py-2 rounded-full bg-red-500 text-white font-semibold">
				Reject
			</button>
			<button type="button" id="btn-submit"
							class="px-6 py-2 rounded-full bg-green-600 hover:bg-green-600/80 text-white font-black">
				Submit
			</button>
		`;
		wrap.appendChild(actions);

		content.appendChild(wrap);

		// document.getElementById('btn-retake').addEventListener('click', retake);
		document.getElementById('btn-retake').addEventListener('click', showRetakeModal);
		document.getElementById('btn-submit').addEventListener('click', submitForm);
	}

  function retake() {
    state.chosen = Object.create(null);
    state.step = 0;
    render();
  }

	function showRetakeModal() {
		retakeModal.classList.remove('hidden');
		retakeModal.classList.add('flex');
	}

	function hideRetakeModal() {
		retakeModal.classList.add('hidden');
		retakeModal.classList.remove('flex');
	}

	if (retakeCancel) retakeCancel.addEventListener('click', hideRetakeModal);
	if (retakeConfirm) retakeConfirm.addEventListener('click', () => {
		hideRetakeModal();
		retake();            // clears selections & restarts
	});

	const thanksModal = document.getElementById('thanks-modal');

	function showThanksModal() {
		thanksModal.classList.remove('hidden');
		thanksModal.classList.add('flex');
	}

  // Skip modal wiring
  btnSkip.addEventListener('click', () => {
    const pos = current();
    skipModal.classList.remove('hidden');
    skipModal.classList.add('flex');
  });
  skipCancel.addEventListener('click', () => {
    skipModal.classList.add('hidden');
    skipModal.classList.remove('flex');
  });
  skipConfirm.addEventListener('click', () => {
    const pid = current().id;
    setChosen(pid, getChosen(pid)); // ensure key exists
    skipModal.classList.add('hidden');
    skipModal.classList.remove('flex');
    nextStep(); // move forward without selection
  });

  // Nav buttons
  btnNext.addEventListener('click', () => {
    // At receipt: submit
    if (state.step === positions.length) {
      submitForm();
      return;
    }
    // On positions: require at least one selection
    if (!hasSelection(current())) return;
    nextStep();
  });
  btnPrev.addEventListener('click', () => {
    if (state.step > 0) {
      state.step -= 1;
      render();
    }
  });

  function nextStep() {
    if (state.step < positions.length) {
      state.step += 1;
      render();
    }
  }

  function submitForm(){
		// Prevent double submit
		const disableAll = () => document.querySelectorAll('button').forEach(b => b.disabled = true);
		disableAll();

		// Clear old inputs
		[...form.querySelectorAll('input[name^="positions["]')].forEach(n => n.remove());

		// Build inputs from state.chosen
		positions.forEach(pos => {
			const pid = pos.id;
			const selected = getChosen(pid);
			if (selected.length === 0) return; // skipped: no inputs
			selected.forEach(cid => {
				const inp = document.createElement('input');
				inp.type = 'hidden';
				inp.name = `positions[${pid}][]`;
				inp.value = String(cid);
				form.appendChild(inp);
			});
		});

		// Prepare FormData (includes _token and election_id already in the form)
		const fd = new FormData(form);

		// Post via fetch so we can show the thanks modal before redirecting
		fetch(form.action, {
			method: 'POST',
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
			body: fd
		})
		.then(async res => {
			// Show the thank-you modal regardless of redirect/ok state
			showThanksModal();

			// Small delay so the voter sees the message
			setTimeout(() => {
				window.location.href = window.__BALLOT__.loginUrl;
			}, 2500);

			// (optional) you could also check for res.ok and handle errors differently
		})
		.catch(() => {
			// If something goes wrong, still try to move on to login
			showThanksModal();
			setTimeout(() => {
				window.location.href = window.__BALLOT__.loginUrl;
			}, 2500);
		});
	}

  // Basic HTML escaper
  function escapeHtml(str){
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Initial render
  render();
})();
</script>
@endsection
