@extends('layouts.voter-app')

@section('content')
  <div class="flex items-center justify-center min-h-[calc(100vh-4rem)] py-8 overflow-x-hidden overflow-y-auto px-4">
    <div class="text-center max-w-md">
      {{-- Illustration --}}
      <div class="mx-auto mb-6 w-40 h-40 rounded-full bg-amber-50 flex items-center justify-center shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160" class="w-28 h-28" aria-hidden="true">
          <!-- Circular backdrop -->
          <circle cx="80" cy="80" r="70" fill="#FDE68A"/>
          <!-- Clipboard base -->
          <rect x="40" y="42" width="80" height="96" rx="12" fill="#92400E"/>
          <rect x="48" y="50" width="64" height="80" rx="8" fill="#F59E0B"/>
          <!-- Clip -->
          <rect x="62" y="30" width="36" height="18" rx="6" fill="#B45309"/>
          <circle cx="80" cy="39" r="6" fill="#FCD34D"/>
          <!-- Exclamation mark -->
          <rect x="77" y="66" width="6" height="28" rx="3" fill="white"/>
          <circle cx="80" cy="100" r="4" fill="white"/>
          <!-- Decorative lines (disabled look) -->
          <rect x="56" y="116" width="48" height="6" rx="3" fill="#FCD34D" opacity=".7"/>
        </svg>
      </div>

      <h1 class="text-2xl font-bold">No candidate registered <br />on this Election</h1>
      <p class="text-gray-600 mt-3">Please contact your admin for more information.</p>
    </div>
  </div>
@endsection
