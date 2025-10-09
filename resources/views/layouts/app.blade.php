<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $title ?? 'Voting System' }}</title>

	@vite(['resources/css/app.css', 'resources/js/app.js'])

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="m-0 min-h-screen bg-gray-100 text-gray-900 font-[Inter]">
	@if(session('error'))
		<script>
			Swal.fire({
				icon: "error",
				text: @json(session('error')),
				confirmButtonColor:"#000000",
				confirmButtonText:@json(session('buttonText')),
				customClass: { htmlContainer: 'text-label', confirmButton: 'confirm-btn' },
				buttonsStyling: false,
				heightAuto: false,
				scrollbarPadding: true,
				position: 'center'
			});
		</script>
	@endif

	@if(session('success'))
		<script>
			Swal.fire({
				icon: "success",
				text: @json(session('success')),
				confirmButtonColor:"#000000",
				confirmButtonText:@json(session('buttonText')),
				customClass: { htmlContainer: 'text-label', confirmButton: 'confirm-btn' },
				buttonsStyling: false,
				heightAuto: false,
				scrollbarPadding: true,
				position: 'center'
			});
		</script>
	@endif

	@php
		$user = Auth::user();
		$username = $user->username ?? ($user->email ?? 'admin');
		$phone = $user->mobile ?? $user->phone ?? '';
		// mask phone: keep first 2 & last 3 digits
		$maskedPhone = $phone
			? (mb_substr($phone,0,2) . str_repeat('*', max(0, mb_strlen($phone)-5)) . mb_substr($phone,-3))
			: null;
	@endphp

	<header class="h-16 bg-[#54585d] text-white flex items-center justify-between px-6 shadow">
		<div class="flex items-center gap-3">
			<img src="{{ asset('logo.png') }}" alt="Logo" class="h-10 w-10 rounded-full object-contain bg-white">
			<span class="uppercase tracking-wide font-extrabold text-sm sm:text-base">
				PASEI Secured Online Voting System
			</span>
		</div>

		<div class="flex items-center gap-3">
			@php
				$fullname = Auth::user()->first_name . ' ' . Auth::user()->last_name;
				$position = ucwords(str_replace(['-','_'], ' ', Auth::user()->type));
			@endphp

			<button
				type="button"
				class="group flex items-center gap-3 cursor-pointer rounded-full px-2 py-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70 transition"
				data-modal-open="#change-password-modal"
				aria-label="Open change password modal">
				<div class="flex flex-col items-end text-right text-xs font-semibold leading-tight">
					<span class="text-white">{{ $fullname }}</span>
					<span class="opacity-80">({{ $position }})</span>
				</div>
				<div class="h-9 w-9 rounded-full bg-[#6c6f74] flex items-center border-2 border-white transition group-hover:text-red-600 group-hover:border-red-600">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
						<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/>
					</svg>
				</div>
			</button>
		</div>
	</header>

	<div class="flex min-h-[calc(100vh-4rem)]">
		<aside class="w-64 bg-[#4a4d52] text-white flex-shrink-0 border-r border-black/10">
			<nav>
				<p class="px-4 py-2.5 text-xs font-extrabold uppercase tracking-wider text-[#9f9f9f] bg-[#403f3b] border-2 border-[#373737]">Reports</p>
				<ul>
					<li class="px-3 py-2 hover:bg-[#d0352f] {{ request()->routeIs('admin.dashboard') ? 'bg-[#d0352f]' : '' }}">
						<a href="{{ route('admin.dashboard') ?? '#' }}" class="flex items-center gap-3">
							<span class="inline-grid place-items-center w-7 h-auto">
								<img src={{ asset('icons/dashboard.png') }} alt="dashboard" width="50" height="50" />
							</span>
							Dashboard
						</a>
					</li>
					<li class="px-3 py-2 hover:bg-[#d0352f] {{ request()->routeIs('admin.votes.index') ? 'bg-[#d0352f]' : '' }}">
						<a href="{{ route('admin.votes.index') ?? '#' }}" class="flex items-center gap-3">
							<span class="inline-grid place-items-center w-7 h-auto">
								<img src={{ asset('icons/votes.png') }} alt="dashboard" width="50" height="50" />
							</span>
							Votes
						</a>
					</li>
				</ul>
				<p class="px-4 py-2.5 text-xs font-extrabold uppercase tracking-wider text-[#9f9f9f] bg-[#403f3b] border-2 border-[#373737]">Manage</p>
				<ul>
					<li class="px-3 py-2 hover:bg-[#d0352f] {{ request()->routeIs('admin.voter-status.index') ? 'bg-[#d0352f]' : '' }}">
						<a href="{{ route('admin.voter-status.index') ?? '#' }}" class="flex items-center gap-3">
							<span class="inline-grid place-items-center w-7 h-auto">
								<img src={{ asset('icons/voters.png') }} alt="dashboard" width="50" height="50" />
							</span>
							Voters Status
						</a>
					</li>
					<li class="px-3 py-2 hover:bg-[#d0352f] {{ request()->routeIs('admin.positions.index') ? 'bg-[#d0352f]' : '' }}">
						<a href="{{ route('admin.positions.index') ?? '#' }}" class="flex items-center gap-3">
							<span class="inline-grid place-items-center w-7 h-auto">
								<img src={{ asset('icons/positions.png') }} alt="dashboard" width="50" height="50" />
							</span>
							Positions
						</a>
					</li>
					<li class="px-3 py-2 hover:bg-[#d0352f] {{ request()->routeIs('admin.candidates.index') ? 'bg-[#d0352f]' : '' }}">
						<a href="{{ route('admin.candidates.index') ?? '#' }}" class="flex items-center gap-3">
							<span class="inline-grid place-items-center w-7 h-auto">
								<img src={{ asset('icons/candidates.png') }} alt="dashboard" width="50" height="50" />
							</span>
							Candidates
						</a>
					</li>
					<li class="px-3 py-2 hover:bg-[#d0352f] {{ request()->routeIs('admin.voters.index') ? 'bg-[#d0352f]' : '' }}">
						<a href="{{ route('admin.voters.index') ?? '#' }}" class="flex items-center gap-3">
							<span class="inline-grid place-items-center w-7 h-auto">
								<img src={{ asset('icons/voter.png') }} alt="dashboard" width="50" height="50" />
							</span>
							Voter
						</a>
					</li>
				</ul>
				<p class="px-4 py-2.5 text-xs font-extrabold uppercase tracking-wider text-[#9f9f9f] bg-[#403f3b] border-2 border-[#373737]">Settings</p>
				<ul>
					<li class="px-3 py-2 hover:bg-[#d0352f] {{ request()->routeIs('admin.index') ? 'bg-[#d0352f]' : '' }}">
						<a href="{{ route('admin.index') ?? '#' }}" class="flex items-center gap-3">
							<span class="inline-grid place-items-center w-7 h-auto">
								<img src={{ asset('icons/admin.png') }} alt="dashboard" width="50" height="50" />
							</span>
							Admin
						</a>
					</li>
					<li class="px-3 py-2 hover:bg-[#d0352f] {{ request()->routeIs('admin.elections.index') ? 'bg-[#d0352f]' : '' }}">
						<a href="{{ route('admin.elections.index') ?? '#' }}" class="flex items-center gap-3">
							<span class="inline-grid place-items-center w-7 h-auto">
								<img src={{ asset('icons/election.png') }} alt="dashboard" width="50" height="50" />
							</span>
							Upcoming Election
						</a>
					</li>
					<li class="px-3 py-2 hover:bg-[#d0352f] {{ request()->routeIs('admin.archives.index') ? 'bg-[#d0352f]' : '' }}">
						<a href="{{ route('admin.archives.index') ?? '#' }}" class="flex items-center gap-3">
							<span class="inline-grid place-items-center w-7 h-auto">
								<img src={{ asset('icons/archives.png') }} alt="dashboard" width="50" height="50" />
							</span>
							Archives
						</a>
					</li>
				</ul>
				<p class="px-4 py-4 text-xs font-extrabold uppercase tracking-wider text-[#9f9f9f] bg-[#403f3b] border-2 border-[#373737]"></p>
				<ul>
					<li class="px-3 py-2 hover:bg-[#d0352f] {{ request()->routeIs('admin.logout') ? 'bg-[#d0352f]' : '' }}">
						<a href="{{ route('admin.logout') ?? '#' }}" class="flex items-center gap-3">
							<span class="inline-grid place-items-center w-7 h-auto">
								<img src={{ asset('icons/admin.png') }} alt="dashboard" width="50" height="50" />
							</span>
							Log out
						</a>
					</li>
				</ul>
			</nav>
		</aside>

		<main class="flex-1 overflow-x-hidden">
			<div class="p-0">
				@yield('content')
				{{ $slot ?? '' }}
			</div>
		</main>
	</div>

	<x-ui.modal
		id="change-password-modal"
		title="Change Password"
		:form="[
			'id' => 'change-password-form',
			'action' => route('admin.change-password'),
			'method' => 'POST',        // <— POST only
			// remove 'spoof' => 'PUT'
			'submitText' => 'Submit'
		]">
		<input type="hidden" name="__action" value="change-password" data-clear-on-close>

		<div>
			<label class="block text-sm mb-1 font-medium">Current Password</label>
			<div class="relative">
				<input
					type="password"
					name="current_password"
					id="cp_current"
					class="w-full border-2 border-gray-300 rounded-md py-2 px-3 pr-10 outline-none"
					placeholder="Enter current password"
					required
					data-clear-on-close>

				<button
					type="button"
					class="absolute inset-y-0 right-2 my-auto text-gray-500"
					data-toggle-password="#cp_current"
					data-toggle-mode="toggle"
					aria-pressed="false"
					aria-label="Show password"
					title="Show password"
				>
					<svg data-eye xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
						<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
						<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
					</svg>

					<svg data-eye-off xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
						<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
					</svg>
				</button>
			</div>
			@error('current_password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
		</div>

		<div>
			<label class="block text-sm mb-1 font-medium">New Password</label>
			<div class="relative">
				<input
					type="password"
					name="password"
					id="cp_new"
					class="w-full border-2 border-gray-300 rounded-md py-2 px-3 pr-10 outline-none"
					placeholder="Enter new password"
					required
					data-clear-on-close>

				<button
					type="button"
					class="absolute inset-y-0 right-2 my-auto text-gray-500"
					data-toggle-password="#cp_new"
					aria-label="Show/Hide">

					<svg data-eye xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
						<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
						<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
					</svg>

					<svg data-eye-off xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
						<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
					</svg>

				</button>
			</div>
			@error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
		</div>

		<div class="pt-2">
			<p class="font-semibold">Authentication</p>

			<div class="flex items-end gap-3 mt-2">
				<div class="flex-1">
					<label class="block text-sm mb-1 font-medium">One Time Passcode</label>
					<input
						type="text"
						inputmode="numeric"
						maxlength="6"
						name="otp" id="cp_otp"
						class="w-full border-2 border-gray-300 rounded-md py-2 px-3 outline-none disabled:bg-gray-100 disabled:text-gray-500"
						placeholder="Enter the 6-digit code"
						{{ old('__action')==='change-password' ? '' : 'disabled' }}
						data-clear-on-close>

					@error('otp') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror

					@if($maskedPhone)
						<p class="text-xs text-gray-600 mt-1">Check OTP on this number {{ $maskedPhone }}</p>
					@endif
				</div>

				<button
					type="button"
					id="cp_send_otp"
					class="h-10 px-4 cursor-pointer rounded-full bg-black text-white disabled:opacity-60"
					data-route="{{ route('admin.send-otp') }}"
					{{ old('__action')==='change-password' ? '' : '' }}>
					Send Code
				</button>
			</div>
		</div>
	</x-ui.modal>

	@push('scripts')
		<script>
			// Re-open modal on validation errors of this form
			@if($errors->any() && old('__action') === 'change-password')
				window.Modal?.openById?.('change-password-modal');
			@endif

			// OTP sending logic with cooldown
			(function(){
				const btn = document.getElementById('cp_send_otp');
				const otpInput = document.getElementById('cp_otp');
				if (!btn || !otpInput) return;

				let cooldown = 0, t;

				async function sendOtp(){
					if (cooldown > 0) return;
					const url = btn.dataset.route;
					if (!url) return;

					btn.disabled = true;
					const label = btn.textContent;
					btn.textContent = 'Sending...';

					try {
						const res = await fetch(url, {
							method: 'POST',
							headers: {
								'X-Requested-With': 'XMLHttpRequest',
								'X-CSRF-TOKEN': '{{ csrf_token() }}',
								'Accept': 'application/json'
							},
							body: new FormData() // no payload needed; server infers user
						});

						const data = await res.json().catch(()=>({ ok:false }));
						if (!res.ok || !data.ok) throw new Error(data.message || 'Failed to send OTP');

						// success: enable OTP input and start cooldown
						otpInput.disabled = false;
						otpInput.focus();

						cooldown = 300; // seconds
						btn.textContent = `Resend in ${cooldown}s`;
						t = setInterval(() => {
							cooldown--;
							if (cooldown <= 0) {
								clearInterval(t);
								btn.disabled = false;
								btn.textContent = 'Resend Code';
								return;
							}
							btn.textContent = `Resend in ${cooldown}s`;
						}, 1000);

						Swal.fire({ icon: 'success', text: 'OTP sent successfully.', timer: 1500, showConfirmButton: false });
					} catch (err) {
						btn.textContent = label;
						btn.disabled = false;
						Swal.fire({ icon: 'error', text: err.message || 'Unable to send OTP right now.' });
					}
				}

				btn.addEventListener('click', sendOtp);
			})();

			(function(){
				function setVisibility(input, btn, show){
					if (!input || !btn) return;
					input.type = show ? 'text' : 'password';
					btn.setAttribute('aria-pressed', String(show));
					btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
					btn.title = show ? 'Hide password' : 'Show password';
					// swap icons
					const eye = btn.querySelector('[data-eye]');
					const eyeOff = btn.querySelector('[data-eye-off]');
					if (eye && eyeOff) {
						eye.classList.toggle('hidden', show);
						eyeOff.classList.toggle('hidden', !show);
					}
				}

				// Click/toggle + optional press-to-hold
				document.addEventListener('click', (e) => {
					const btn = e.target.closest('[data-toggle-password]');
					if (!btn) return;

					const targetSel = btn.getAttribute('data-toggle-password');
					const input = document.querySelector(targetSel);
					if (!input) return;

					const mode = btn.getAttribute('data-toggle-mode') || 'toggle';
					if (mode === 'toggle') {
						const show = input.type === 'password';
						setVisibility(input, btn, show);
					}
				});

				// Support press-and-hold if data-toggle-mode="hold"
				function downHandler(e){
					const btn = e.target.closest('[data-toggle-password][data-toggle-mode="hold"]');
					if (!btn) return;
					const input = document.querySelector(btn.getAttribute('data-toggle-password'));
					if (!input) return;
					setVisibility(input, btn, true);
				}
				function upHandler(e){
					document.querySelectorAll('[data-toggle-password][data-toggle-mode="hold"]').forEach((btn) => {
						const input = document.querySelector(btn.getAttribute('data-toggle-password'));
						if (input) setVisibility(input, btn, false);
					});
				}
				document.addEventListener('pointerdown', downHandler);
				document.addEventListener('pointerup', upHandler);
				document.addEventListener('pointercancel', upHandler);

				// Ensure initial state is correct on load (icons match type)
				window.addEventListener('DOMContentLoaded', () => {
					document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
						const input = document.querySelector(btn.getAttribute('data-toggle-password'));
						if (input) setVisibility(input, btn, input.type === 'text');
					});
				});
			})();
		</script>
	@endpush

	@stack('scripts')
</body>
</html>
