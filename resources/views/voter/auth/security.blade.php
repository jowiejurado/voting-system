@php($title = 'Security Question | Voting System')
@extends('layouts.voter-auth')

@section('content')
<div class="flex flex-col md:flex-row items-center justify-center mx-auto gap-8 md:gap-16 lg:gap-[120px] w-full max-w-5xl px-2">
  <img src="{{ asset('logo.png') }}" alt="Logo" width="350" height="350" class="bg-white rounded-full p-0 m-0 w-40 h-40 sm:w-52 sm:h-52 md:w-64 md:h-64 lg:w-[350px] lg:h-[350px] object-contain shrink-0" />
  <div class="flex flex-col justify-center items-center w-full min-w-0">
    <h1 class="text-base sm:text-xl md:text-2xl uppercase text-black font-black text-center mb-4 sm:mb-8 px-1 leading-tight">PASEI SECURED ONLINE VOTING SYSTEM</h1>

    <div class="bg-white shadow-2xl p-6 sm:p-[32px] rounded-4xl max-w-[500px] w-full items-center flex flex-col">
      <form method="post" action="{{ route('voter.security.verify') }}" class="flex flex-col items-stretch gap-[16px] w-full">
        @csrf
        <h4 class="text-lg text-black font-bold">3rd Step Verification - Security Question</h4>
        <label class="text-sm text-gray-700">{{ $question->question }}</label>

        <input
          type="text"
          name="answer"
          required
          minlength="2"
          maxlength="255"
          placeholder="Type your answer (min 2 chars)"
          class="py-[16px] px-[24px] rounded-3xl bg-gray-100 text-black outline-none border-none"
        />
        <p class="text-xs text-gray-500">Hint: Your answer is not case-sensitive. Avoid trailing spaces.</p>

        <button class="inline-block py-3 px-6 rounded-3xl bg-black text-white font-semibold" type="submit">
          Verify & Proceed
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
