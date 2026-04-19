@php($title = 'Log In | Voting System')
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
    <form id="admin-login-form" method="post" action="{{ $loginAction ?? route('auth.login.submit') }}" class="flex flex-col items-center gap-[24px]">
      @csrf
      <h4 class="text-lg text-black font-bold">Sign in</h4>

      <input type="text" id="adminId" name="adminId" required placeholder="ID"
             class="w-100 py-[16px] px-[24px] rounded-3xl bg-gray-100 text-black outline-none border-none">

      <div class="relative w-100">
        <input type="password" id="password" name="password" required placeholder="PASSWORD" autocomplete="current-password"
               class="w-100 py-[16px] pl-[24px] pr-[52px] rounded-3xl bg-gray-100 text-black outline-none border-none">
        <button type="button" id="password-toggle" class="absolute inset-y-0 right-0 flex items-center justify-center px-3 text-gray-600 hover:text-black cursor-pointer bg-transparent border-none rounded-r-3xl"
                aria-label="Show password" aria-pressed="false">
          <span class="password-toggle-icon-show" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
          </span>
          <span class="password-toggle-icon-hide hidden" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
              <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
          </span>
        </button>
      </div>

      @if($recaptchaEnabled ?? true)
        {!! NoCaptcha::display([
          'data-size'  => 'normal',
        ]) !!}

        @error('g-recaptcha-response')
          <div class="text-red-600 text-sm -mt-2">{{ $message }}</div>
        @enderror
      @endif

			<button class="inline-block py-4 px-8 rounded-3xl border-none bg-black text-white cursor-pointer font-semibold" type="submit">
        Proceed
      </button>
      <a href="{{ route('auth.password.request') }}" class="text-blue-600 font-medium text-sm">Forgot password?</a>
    </form>
  </div>
</div>
@endsection

@push('scripts')
  @if($recaptchaEnabled ?? true)
    {!! NoCaptcha::renderJs() !!}
  @endif

	<script>
		window.addEventListener('load', () => {
			const wrap = document.getElementById('auth-anim');
			const SHOW_AFTER_MS = 2500;
			setTimeout(() => wrap.classList.add('show-form'), SHOW_AFTER_MS);

			const passwordInput = document.getElementById('password');
			const passwordToggle = document.getElementById('password-toggle');
			if (passwordInput && passwordToggle) {
				const iconShow = passwordToggle.querySelector('.password-toggle-icon-show');
				const iconHide = passwordToggle.querySelector('.password-toggle-icon-hide');
				passwordToggle.addEventListener('click', () => {
					const willShow = passwordInput.type === 'password';
					passwordInput.type = willShow ? 'text' : 'password';
					passwordToggle.setAttribute('aria-pressed', String(willShow));
					passwordToggle.setAttribute('aria-label', willShow ? 'Hide password' : 'Show password');
					iconShow.classList.toggle('hidden', willShow);
					iconHide.classList.toggle('hidden', !willShow);
				});
			}
		});
	</script>
@endpush
