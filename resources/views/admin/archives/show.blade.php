@php($title = 'Election Result | ' . $election->title)
@extends('layouts.app')

@section('content')
  <x-slot:nav>
    <a href="{{ route('admin.archives.index') }}" class="btn secondary mr-2">Back to Archives</a>
    <form method="post" action="{{ route('admin.logout') }}" style="display:inline">
      @csrf
      <button class="btn secondary" type="submit">Logout</button>
    </form>
  </x-slot:nav>

  <div class="px-3 sm:px-6 lg:px-10 pt-5 pb-8 flex flex-col gap-6 min-w-0">

    {{-- Back button inside page --}}
    <div>
      <a href="{{ route('admin.archives.index') }}"
         class="inline-flex items-center px-4 py-2 rounded-full bg-gray-200 hover:bg-gray-300 text-sm text-gray-800">
        ← Back
      </a>
    </div>

    {{-- Election header --}}
    <div class="border-b border-gray-300 pb-4">
      <h1 class="text-2xl font-black text-[#0b252a]">
        {{ $election->title }}
      </h1>
      <div class="mt-1 text-sm text-gray-600">
        {{ \Carbon\Carbon::parse($election->start_date)->format('F d, Y') }}
        @if($election->start_time && $election->end_time)
          · {{ $election->start_time }} – {{ $election->end_time }}
        @endif
      </div>
    </div>

    @if(empty($positionResults))
      <p class="text-sm text-gray-500">
        No vote data recorded for this election.
      </p>
    @endif

    {{-- Two tables per row on md+ screens --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
      @foreach($positionResults as $positionResult)
        <?php
          $rows = $positionResult['rows'];
          $topVotes = $rows[0]['votes'] ?? 0;
        ?>

        <div class="border border-gray-300 rounded-2xl overflow-x-auto bg-white shadow-sm min-w-0">
          <div class="px-4 py-3 bg-gray-100 border-b border-gray-200 flex items-center justify-between">
            <div class="font-semibold text-gray-800">
              {{ $positionResult['position'] }}
            </div>
            @if($topVotes > 0)
              <div class="text-xs text-gray-500">
                Highest votes: {{ $topVotes }}
              </div>
            @endif
          </div>

          <table class="min-w-full text-sm">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-200">
                <th class="py-2 px-4 text-left">Candidate</th>
                <th class="py-2 px-4 text-center w-32">Votes</th>
                <th class="py-2 px-4 text-center w-32">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $index => $row)
                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} border-b border-gray-100">
                  <td class="py-2 px-4">
                    {{ $row['name'] }}
                  </td>
                  <td class="py-2 px-4 text-center font-semibold">
                    {{ $row['votes'] }}
                  </td>
                  <td class="py-2 px-4 text-center">
                    @if($index === 0 && $topVotes > 0)
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        Winner
                      </span>
                    @else
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-500">
                        —
                      </span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="py-3 px-4 text-center text-gray-500">
                    No candidates for this position.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      @endforeach
    </div>
  </div>
@endsection
