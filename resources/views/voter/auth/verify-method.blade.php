@php($title = 'Select Verification Method | Voting System')
@extends('layouts.voter-auth')

@section('content')
<div class="flex flex-col md:flex-row items-center justify-center mx-auto gap-8 md:gap-16 lg:gap-[120px] w-full max-w-5xl px-2">
  <img src="{{ asset('logo.png') }}" alt="Logo" width="350" height="350" class="bg-white rounded-full p-0 m-0 w-40 h-40 sm:w-52 sm:h-52 md:w-64 md:h-64 lg:w-[350px] lg:h-[350px] object-contain shrink-0" />
  <div class="flex flex-col justify-center items-center w-full min-w-0">
    <h1 class="text-base sm:text-xl md:text-2xl uppercase text-black font-black text-center mb-4 sm:mb-8 px-1 leading-tight">PASEI SECURED ONLINE VOTING SYSTEM</h1>

    <div class="bg-white shadow-2xl p-6 sm:p-[32px] rounded-4xl max-w-[500px] w-full flex flex-col gap-4">
      <h4 class="text-lg text-black font-bold">3rd Step Verification - Choose Method</h4>
      <p class="text-sm text-gray-600">You can verify via facial recognition or answer your registered security question.</p>

      <div class="flex flex-col gap-3">
        <a href="{{ route('voter.face') }}" class="inline-block text-center py-3 px-6 rounded-3xl bg-black text-white font-semibold">
          Use Facial Recognition
        </a>
        <a href="{{ route('voter.security.show') }}" class="inline-block text-center py-3 px-6 rounded-3xl bg-gray-900 text-white font-semibold">
          Answer Security Question
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
