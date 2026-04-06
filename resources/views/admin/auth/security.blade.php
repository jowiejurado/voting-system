@php($title = 'Security Question | Voting System')
@extends('layouts.auth')

@section('content')
<div class="flex items-center mx-auto gap-[120px]">
  <img src="{{ asset('logo.png') }}" alt="Logo" width="350" height="350" class="bg-white rounded-full p-0 m-0" />
  <div class="bg-white shadow-2xl p-[32px] rounded-4xl max-w-[500px] items-center flex flex-col w-full md:w-auto">
    <form method="post" action="{{ $securityVerifyRoute }}" class="flex flex-col items-stretch gap-[16px] w-full">
      @csrf
      <h4 class="text-lg text-black font-bold">Security Question</h4>
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
@endsection
