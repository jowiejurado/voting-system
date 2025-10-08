<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $title ?? 'Voting System' }}</title>

	@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/modal.js'])

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
			<div class="flex flex-col items-center text-center text-xs font-semibold">
				{{ $fullname }}
				<p>({{ $position }})</p>
			</div>
			<div class="h-9 w-9 rounded-full bg-[#6c6f74] flex items-center border-2 border-white">
				<svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-white" viewBox="0 0 24 24" fill="currentColor">
					<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/>
				</svg>
			</div>
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

	@stack('scripts')
</body>
</html>
