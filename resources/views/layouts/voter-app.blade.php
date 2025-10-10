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
<body class="m-0 min-h-screen bg-[#fcfbfc] text-gray-900 font-[Inter]">
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
		$phone = $user->mobile ?? $user->phone ?? '';
		// mask phone: keep first 2 & last 3 digits
		$maskedPhone = $phone
			? (mb_substr($phone,0,2) . str_repeat('*', max(0, mb_strlen($phone)-5)) . mb_substr($phone,-3))
			: null;
	@endphp

	<header class="h-16 bg-black text-white flex items-center justify-between px-6 shadow">
		<div class="flex items-center gap-3">
			<img src="{{ asset('logo.png') }}" alt="Logo" class="h-10 w-10 rounded-full object-contain bg-white">
			<span class="uppercase tracking-wide font-extrabold text-sm sm:text-base">
				PASEI Secured Online Voting System
			</span>
		</div>

		<div class="relative" id="avatar-wrap">
			<!-- Trigger -->
			<button
				type="button"
				id="avatar-btn"
				class="group flex items-center gap-3 cursor-pointer rounded-full px-2 py-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70 transition"
				aria-haspopup="menu"
				aria-expanded="false"
				aria-controls="avatar-menu"
			>
				<div class="h-9 w-9 rounded-full bg-transparent flex items-center border-2 border-white transition group-hover:text-red-600 group-hover:border-red-600">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
						<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/>
					</svg>
				</div>
			</button>

			<!-- Menu -->
			<div
				id="avatar-menu"
				role="menu"
				aria-labelledby="avatar-btn"
				class="absolute right-0 mt-2 w-56 bg-white text-gray-900 rounded-xl shadow-lg border border-gray-200 hidden"
			>
				<!-- Pointer -->
				<div class="absolute -top-2 right-4 w-4 h-4 bg-white rotate-45 border-t border-l border-gray-200"></div>

				<button
					type="button"
					role="menuitem"
					id="menu-account"
					class="w-full cursor-pointer flex items-center gap-3 px-4 py-3 rounded-t-xl hover:bg-gray-100 text-sm font-semibold"
					data-modal-open="#change-password-modal"
					aria-label="Open change password modal"
				>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
						<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
						<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
					</svg>
					Account Settings
				</button>

				<form method="POST" action="{{ route('voter.logout') }}">
					@csrf
					<button
						type="submit"
						role="menuitem"
						id="menu-logout"
						class="w-full cursor-pointer flex items-center gap-3 px-4 py-3 rounded-b-xl hover:bg-gray-100 text-sm font-semibold text-black"
					>
						<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
							<path d="M16 13v-2H7V8L3 12l4 4v-3h9Zm3-10H11a2 2 0 0 0-2 2v3h2V5h8v14h-8v-3H9v3a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z"/>
						</svg>
						Log out
					</button>
				</form>
			</div>
		</div>
	</header>

	<main class="min-h-[calc(100vh-4rem)] flex items-center justify-center overflow-hidden">
		@yield('content')
		{{ $slot ?? '' }}
	</main>

	<x-ui.modal
		id="change-password-modal"
		title="Change Password"
		:form="[
			'id' => 'change-password-form',
			'action' => route('voter.change-password'),
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
					data-route="{{ route('voter.send-otp') }}"
					{{ old('__action')==='change-password' ? '' : '' }}>
					Send Code
				</button>
			</div>
		</div>
	</x-ui.modal>

	@stack('scripts')
	<script>
		@if($errors->any() && old('__action') === 'change-password')
			window.Modal?.openById?.('change-password-modal');
		@endif

		(function(){
			const btn   = document.getElementById('avatar-btn');
			const menu  = document.getElementById('avatar-menu');

			if (!btn || !menu) return;

			function openMenu() {
				menu.classList.remove('hidden');
				btn.setAttribute('aria-expanded', 'true');
				// focus first item for accessibility
				const firstItem = menu.querySelector('[role="menuitem"]');
				firstItem && firstItem.focus();
			}
			function closeMenu() {
				menu.classList.add('hidden');
				btn.setAttribute('aria-expanded', 'false');
			}
			function toggleMenu() {
				const isOpen = !menu.classList.contains('hidden');
				isOpen ? closeMenu() : openMenu();
			}

			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				toggleMenu();
			});

			// Close on click outside
			document.addEventListener('click', (e) => {
				if (!menu.classList.contains('hidden')) {
					const within = e.target.closest('#avatar-wrap');
					if (!within) closeMenu();
				}
			});

			// Keyboard
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape') closeMenu();
			});

			// Trap tab focus inside menu when open (lightweight)
			menu.addEventListener('keydown', (e) => {
				if (e.key !== 'Tab') return;
				const items = [...menu.querySelectorAll('[role="menuitem"]')];
				if (!items.length) return;
				const idx = items.indexOf(document.activeElement);
				if (e.shiftKey) {
					// back
					if (idx === 0 || idx === -1) { e.preventDefault(); items[items.length-1].focus(); }
				} else {
					// forward
					if (idx === items.length-1) { e.preventDefault(); items[0].focus(); }
				}
			});
		})();

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
					if (!res.ok || !data.success) throw new Error(data.message || 'Failed to send OTP');

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
</body>
</html>
