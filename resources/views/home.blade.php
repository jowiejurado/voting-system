@php($title = 'PASEI Voting System')

@extends('layouts.auth')

@section('content')
<div class="w-full">
  <div class="mx-auto max-w-4xl w-full px-6 py-12">
    <div class="rounded-3xl bg-white/80 backdrop-blur-sm shadow-2xl ring-1 ring-black/5">
      <div class="px-8 pt-10 pb-6 text-center">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
          Welcome to PASEI Secured Online Voting System
        </h1>
        <p class="mt-3 text-slate-700">
          Choose a panel to continue.
        </p>
      </div>

      <div class="px-8 pb-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <a
            href="{{ route('admin.login') }}"
            class="group relative block rounded-2xl border border-slate-200 bg-white hover:border-indigo-300 shadow-sm hover:shadow-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
            aria-label="Admin Login"
          >
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600/10 text-indigo-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.75a3 3 0 00-3 3v1.5h6v-1.5a3 3 0 00-3-3z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 11.25h10.5v5.25a3 3 0 01-3 3h-4.5a3 3 0 01-3-3v-5.25z" />
                    </svg>
                  </span>
                  <div>
                    <h2 class="text-lg font-semibold text-slate-900">Admin Panel</h2>
                  </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400 group-hover:text-indigo-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </div>
          </a>

          {{-- Voter Card --}}
          <a
            href="{{ route('voter.login') }}"
            class="group relative block rounded-2xl border border-slate-200 bg-white hover:border-emerald-300 shadow-sm hover:shadow-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
            aria-label="Voter Login"
          >
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-600/10 text-emerald-700">
                    <!-- Ballot/check icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.5 6h9a1.5 1.5 0 011.5 1.5v9A1.5 1.5 0 0116.5 18h-9A1.5 1.5 0 016 16.5v-9A1.5 1.5 0 017.5 6z" />
                    </svg>
                  </span>
                  <div>
                    <h2 class="text-lg font-semibold text-slate-900">Voter Panel</h2>
                  </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400 group-hover:text-emerald-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </div>
          </a>
        </div>

        <p class="mt-8 text-center text-xs text-slate-600">
          © {{ date('Y') }} PASEI. Your vote is confidential and protected.
        </p>
      </div>
    </div>
  </div>
</div>
@endsection
