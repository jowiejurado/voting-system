@php($title = 'Admin - Log In | Voting System')
@extends('layouts.auth')

@section('content')
<style>
  #auth-anim{
    --panelW: 520px;
    --gap: 0px;
    max-width: 1100px;
    width: 100%;
    margin-inline: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--gap);
    transition: gap .6s ease;
  }
  #auth-anim.show-form{ --gap: 120px; }

  #auth-anim .logo{
    transform: translateX(calc((var(--panelW) + var(--gap)) / 2));
    transition: transform .7s cubic-bezier(.22,.61,.36,1);
    will-change: transform;
  }

  #auth-anim.show-form .logo{
    transform: translateX(0);
  }

  #auth-anim .panel{
    width: var(--panelW);
    opacity: 0;
    visibility: hidden;
    transition: opacity .55s ease;
    will-change: opacity;
    pointer-events: none;
  }
  #auth-anim.show-form .panel{
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
  }

  @media (max-width: 767px){
    #auth-anim{
      flex-direction: column;
      --gap: 24px;
    }
    #auth-anim .logo{ transform: none; }
    #auth-anim .panel{ width: 100%; }
  }

  @media (prefers-reduced-motion: reduce){
    #auth-anim, #auth-anim .logo, #auth-anim .panel{ transition: none !important; }
  }
</style>

<div id="auth-anim">
  <img
    src="{{ asset('logo.png') }}"
    alt="Logo"
    width="350"
    height="350"
    class="logo bg-white rounded-full p-0 m-0"
  />

  <div class="panel bg-white shadow-2xl p-[32px] rounded-4xl max-w-[500px] w-full md:w-auto">
    <form id="admin-login-form" method="post" action="{{ route('admin.login.submit') }}" class="flex flex-col items-center gap-[24px]">
      @csrf
      <h4 class="text-lg text-black font-bold">Admin 1st Step Authentication - Log in</h4>

      <input type="text" id="adminId" name="adminId" required placeholder="ADMIN ID"
             class="w-100 py-[16px] px-[24px] rounded-3xl bg-gray-100 text-black outline-none border-none">

      <input type="password" id="password" name="password" required placeholder="PASSWORD"
             class="w-100 py-[16px] px-[24px] rounded-3xl bg-gray-100 text-black outline-none border-none">

     {{-- reCAPTCHA v2 Checkbox --}}
      {!! NoCaptcha::display([
				// Optional attributes:
				// 'data-theme' => 'light', // or 'dark'
				'data-size'  => 'normal', // or 'compact'
      ]) !!}

      @error('g-recaptcha-response')
        <div class="text-red-600 text-sm -mt-2">{{ $message }}</div>
      @enderror

			<button class="inline-block py-4 px-8 rounded-3xl border-none bg-black text-white cursor-pointer font-semibold" type="submit">
        Proceed
      </button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
  {{-- Loads the reCAPTCHA v2 script --}}
  {!! NoCaptcha::renderJs() !!}

	<script>
		// Wait 5s after all assets (incl. logo) are loaded, then run the animation.
		window.addEventListener('load', () => {
			const wrap = document.getElementById('auth-anim');
			const SHOW_AFTER_MS = 2500; // 5 seconds
			setTimeout(() => wrap.classList.add('show-form'), SHOW_AFTER_MS);
		});
	</script>
@endpush
