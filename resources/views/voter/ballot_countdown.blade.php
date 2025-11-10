@php($title = 'Ballot Countdown | Voting System')

@extends('layouts.voter-app')

@section('content')
<div class="flex items-center justify-center h-[calc(100vh-4rem)] overflow-hidden px-4">
  <div class="text-center max-w-xl">
    <h1 class="text-3xl font-black mb-2">
      {{ $election->title ?? 'Upcoming Election' }}
    </h1>
    <p class="text-gray-600 mb-6">
      Voting opens at
      <strong>
        {{ \Carbon\Carbon::createFromTimestampMs($startTimestampMs, 'Asia/Manila')->format('F j, Y g:i A') }}
        (Asia/Manila)
      </strong>
    </p>

		<div id="countdown" class="flex justify-center items-stretch gap-4 mb-6 flex-nowrap overflow-x-auto">
			<div class="bg-white rounded-2xl shadow border p-4 w-28 text-center">
				<div id="cd-days" class="text-4xl font-black">0</div>
				<div class="text-gray-600 font-semibold">Days</div>
			</div>
			<div class="bg-white rounded-2xl shadow border p-4 w-28 text-center">
				<div id="cd-hours" class="text-4xl font-black">0</div>
				<div class="text-gray-600 font-semibold">Hours</div>
			</div>
			<div class="bg-white rounded-2xl shadow border p-4 w-28 text-center">
				<div id="cd-minutes" class="text-4xl font-black">0</div>
				<div class="text-gray-600 font-semibold">Minutes</div>
			</div>
			<div class="bg-white rounded-2xl shadow border p-4 w-28 text-center">
				<div id="cd-seconds" class="text-4xl font-black">0</div>
				<div class="text-gray-600 font-semibold">Seconds</div>
			</div>
		</div>

    <p class="text-gray-700">
      When the timer reaches zero, the ballot will appear automatically.
    </p>
  </div>
</div>

<script>
(function(){
  const target = Number(@json($startTimestampMs));

  const elD = document.getElementById('cd-days');
  const elH = document.getElementById('cd-hours');
  const elM = document.getElementById('cd-minutes');
  const elS = document.getElementById('cd-seconds');

  function pad(n){ return n.toString().padStart(2, '0'); }

  function tick() {
    const now = Date.now();
    let diff = target - now;

    if (diff <= 0) {
      window.location.reload();
      return;
    }

    const secTotal = Math.floor(diff / 1000);
    const days  = Math.floor(secTotal / 86400);
    const hours = Math.floor((secTotal % 86400) / 3600);
    const mins  = Math.floor((secTotal % 3600) / 60);
    const secs  = secTotal % 60;

    elD.textContent = String(days);
    elH.textContent = pad(hours);
    elM.textContent = pad(mins);
    elS.textContent = pad(secs);
  }

  tick();
  const timer = setInterval(tick, 1000);

  window.addEventListener('beforeunload', () => clearInterval(timer));
})();
</script>
@endsection
