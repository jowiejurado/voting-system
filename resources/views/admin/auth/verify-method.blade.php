@php($title = 'Select Verification | Voting System')
@php($isVoterFlow = str_starts_with($verifyBeginRoute ?? '', 'voter.'))
@extends('layouts.auth')

@section('content')
<div class="flex flex-col md:flex-row items-center justify-center mx-auto gap-8 md:gap-16 lg:gap-[120px] w-full max-w-5xl px-2">
  <img src="{{ asset('logo.png') }}" alt="Logo" width="350" height="350" class="bg-white rounded-full p-0 m-0 w-40 h-40 sm:w-52 sm:h-52 md:w-64 md:h-64 lg:w-[350px] lg:h-[350px] object-contain shrink-0" />
  <div class="bg-white shadow-2xl p-6 sm:p-[32px] rounded-4xl max-w-[500px] w-full flex flex-col gap-4 min-w-0">
    <h4 class="text-lg text-black font-bold">2nd Step Verification — Choose Method</h4>

    @if($isVoterFlow)
      <p class="text-sm text-gray-600">
        Continue with face verification, OTP verification, or your security question.
      </p>
      <div class="flex flex-col gap-3">
        @if($hasRegisteredFace ?? false)
          <a href="{{ route($verifyBeginRoute, ['method' => 'face']) }}" class="inline-block text-center py-3 px-6 rounded-3xl bg-black text-white font-semibold">
            1 — Face verification
          </a>
        @else
          <span class="inline-block text-center py-3 px-6 rounded-3xl bg-gray-200 text-gray-500 font-semibold cursor-not-allowed" title="Face is not registered for this account.">
            1 — Face verification (not registered)
          </span>
        @endif

        <a href="{{ route($verifyBeginRoute, ['method' => 'otp']) }}" class="inline-block text-center py-3 px-6 rounded-3xl bg-gray-900 text-white font-semibold">
          2 — OTP verification
        </a>

        @if($hasSecurityQuestions ?? false)
          <a href="{{ route($verifyBeginRoute, ['method' => 'security']) }}" class="inline-block text-center py-3 px-6 rounded-3xl bg-gray-100 text-black font-semibold border border-gray-200">
            3 — Security questions
          </a>
        @else
          <span class="inline-block text-center py-3 px-6 rounded-3xl bg-gray-200 text-gray-500 font-semibold cursor-not-allowed" title="No security questions are set up for this account.">
            3 — Security questions (not set up)
          </span>
        @endif
      </div>
    @else
      @if($hasRegisteredFace ?? false)
        <p class="text-sm text-gray-600">Continue with face verification or OTP verification.</p>
      @else
        <p class="text-sm text-gray-600">Continue with OTP verification.</p>
      @endif

      <div class="flex flex-col gap-3">
        @if($hasRegisteredFace ?? false)
          <a href="{{ route($verifyBeginRoute, ['method' => 'face']) }}" class="inline-block text-center py-3 px-6 rounded-3xl bg-black text-white font-semibold">
            Face verification
          </a>
        @endif
        <a href="{{ route($verifyBeginRoute, ['method' => 'otp']) }}" class="inline-block text-center py-3 px-6 rounded-3xl bg-gray-900 text-white font-semibold">
          OTP verification
        </a>
      </div>
    @endif
  </div>
</div>
@endsection
