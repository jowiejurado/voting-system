@extends('layouts.voter-app')

@section('content')
  <div class="flex items-center justify-center h-[calc(100vh-4rem)] overflow-hidden px-4">
    <div class="text-center max-w-md">
      {{-- Illustration --}}
      <div class="mx-auto mb-6 w-40 h-40 rounded-full bg-green-50 flex items-center justify-center shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160" class="w-28 h-28" aria-hidden="true">
          <!-- Circle backdrop -->
          <circle cx="80" cy="80" r="70" fill="#BBF7D0"/>
          <!-- Ballot box -->
          <rect x="30" y="70" width="100" height="60" rx="10" fill="#065F46"/>
          <rect x="40" y="80" width="80" height="40" rx="6" fill="#10B981"/>
          <!-- Paper -->
          <rect x="52" y="40" width="56" height="38" rx="6" fill="white"/>
          <!-- Checkmark -->
          <path d="M60 62 l10 10 22-22" fill="none" stroke="#10B981" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
          <!-- Slot -->
          <rect x="50" y="68" width="60" height="8" rx="4" fill="#064E3B"/>
        </svg>
      </div>

      <h1 class="text-2xl font-bold">You have already voted</h1>
      <p class="text-gray-600 mt-3">Please contact your admin for more information.</p>
    </div>
  </div>
@endsection
