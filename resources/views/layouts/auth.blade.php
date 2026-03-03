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
<body class="min-h-screen m-0 font-[Inter] antialiased">
	@if(session('error'))
		<script>
			Swal.fire({
				icon: "error",
				text: @json(session('error')),
				confirmButtonColor:"#000000",
				confirmButtonText:@json(session('buttonText')) ?? 'Proceed',
				customClass: {
					htmlContainer: 'text-label',
					confirmButton: 'confirm-btn',
				},
				buttonsStyling: false,
				heightAuto: false,
				scrollbarPadding: true
			});
		</script>
	@endif

	@if(session('success'))
		<script>
			Swal.fire({
				icon: "success",
				text: @json(session('success')),
				confirmButtonColor:"#000000",
				confirmButtonText:@json(session('buttonText')) ?? 'Proceed',
				customClass: {
					htmlContainer: 'text-label',
					confirmButton: 'confirm-btn',
				},
				buttonsStyling: false,
				heightAuto: false,
				scrollbarPadding: true
			});
		</script>
	@endif

	<main class="relative" style="background-image:url('{{ asset('images/bg-image.jpg') }}'); background-size:cover; background-position:center;">
		@php
			$loginRoute = request()->getHost() === config('domains.admin') ? 'admin.login' : 'voter.login';
			$isLoginPage = in_array(Route::currentRouteName(), ['admin.login', 'voter.login'], true);
		@endphp
		@if(!$isLoginPage)
			<a href="{{ route($loginRoute) }}" class="absolute m-5 rounded-full p-2 cursor-pointer text-black bg-white flex items-center justify-center text-center" aria-label="Back to login">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
					<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
				</svg>
			</a>
		@endif

		<div class="min-h-screen flex items-center">
			@yield('content')
		</div>
  </main>

	@stack('scripts')
</body>
</html>
