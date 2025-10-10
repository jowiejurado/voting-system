@extends('layouts.voter-app')

@section('content')
  <div class="flex items-center justify-center h-[calc(100vh-4rem)] overflow-hidden px-4">
    <div class="text-center max-w-lg">
      {{-- Illustration --}}
      <div class="mx-auto mb-6 w-44 h-44 rounded-full bg-blue-50 flex items-center justify-center shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160" class="w-28 h-28" aria-hidden="true">
          <!-- Backdrop circle -->
          <circle cx="80" cy="80" r="70" fill="#DBEAFE"/>
          <!-- Calendar -->
          <rect x="32" y="48" width="96" height="70" rx="10" fill="#1D4ED8"/>
          <rect x="40" y="66" width="80" height="44" rx="6" fill="white"/>
          <!-- Calendar rings -->
          <rect x="52" y="40" width="12" height="16" rx="4" fill="#1D4ED8"/>
          <rect x="96" y="40" width="12" height="16" rx="4" fill="#1D4ED8"/>
          <!-- Crossed day (no active) -->
          <line x1="50" y1="70" x2="110" y2="110" stroke="#EF4444" stroke-width="6" stroke-linecap="round"/>
          <line x1="110" y1="70" x2="50"  y2="110" stroke="#EF4444" stroke-width="6" stroke-linecap="round"/>
          <!-- Clock -->
          <circle cx="120" cy="52" r="14" fill="white" stroke="#1D4ED8" stroke-width="4"/>
          <line x1="120" y1="52" x2="120" y2="44" stroke="#1D4ED8" stroke-width="3" stroke-linecap="round"/>
          <line x1="120" y1="52" x2="128" y2="52" stroke="#1D4ED8" stroke-width="3" stroke-linecap="round"/>
        </svg>
      </div>

      <h1 class="text-2xl font-bold">No Active Election</h1>
      <p class="text-gray-600 mt-3">
        There is currently no active election. Please check back later.
      </p>
    </div>
  </div>
@endsection
