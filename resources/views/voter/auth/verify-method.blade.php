@php($title = 'Select Verification Method | Voting System')
@extends('layouts.voter-auth')

@section('content')
<div class="flex items-center mx-auto gap-[120px]">
  <img src="{{ asset('logo.png') }}" alt="Logo" width="350" height="350" class="bg-white rounded-full p-0 m-0" />
  <div class="flex flex-col justify-center items-center">
    <h1 class="text-2xl uppercase text-black font-black text-center mb-8">PASEI SECURED ONLINE VOTING SYSTEM</h1>

    <div class="bg-white shadow-2xl p-[32px] rounded-4xl max-w-[500px] w-full md:w-auto flex flex-col gap-4">
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
