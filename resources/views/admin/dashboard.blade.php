@php($title = 'Admin Dashboard')
@extends('layouts.app')

@section('content')
  <x-slot:nav>
    <form method="post" action="{{ route('admin.logout') }}" style="display:inline">
      @csrf
      <button class="btn secondary" type="submit">Logout</button>
    </form>
  </x-slot:nav>

  <div>
    {{-- Summary tiles --}}
    <div class="flex items-center justify-around border-b-2 border-b-black py-6">
      <a href="/admin/positions" class="flex justify-between gap-9 bg-[#545454] px-4 py-6 rounded-4xl">
        <div class="text-white">
          <div style="font-size:28px;font-weight:700">{{ $stats['positions'] ?? 0 }}</div>
          <div class="bg-[#243539] px-4 py-1 rounded-4xl text-xs">No. of Positions</div>
        </div>
        <div class="max-w-[70px] h-auto">
          <img src={{ asset('icons/positions.png') }} alt="icon" width="70" height="70" />
        </div>
      </a>

      <a href="/admin/candidates" class="flex justify-between gap-9 bg-[#545454] px-4 py-6 rounded-4xl">
        <div class="text-white">
          <div style="font-size:28px;font-weight:700">{{ $stats['candidates'] ?? 0 }}</div>
          <div class="bg-[#243539] px-4 py-1 rounded-4xl text-xs">No. of Candidates</div>
        </div>
        <div class="max-w-[70px] h-auto">
          <img src={{ asset('icons/candidates.png') }} alt="icon" width="70" height="70" />
        </div>
      </a>

      <a href="/admin/voters" class="flex justify-between gap-9 bg-[#545454] px-4 py-6 rounded-4xl">
        <div class="text-white">
          <div style="font-size:28px;font-weight:700">{{ $stats['voters'] ?? 0 }}</div>
          <div class="bg-[#243539] px-4 py-1 rounded-4xl text-xs">Total Voters</div>
        </div>
        <div class="max-w-[70px] h-auto">
          <img src={{ asset('icons/voters.png') }} alt="icon" width="70" height="70" />
        </div>
      </a>

      <a href="/admin/voter-status" class="flex justify-between gap-9 bg-[#545454] px-4 py-6 rounded-4xl">
        <div class="text-white">
          <div style="font-size:28px;font-weight:700">{{ $stats['voted'] ?? 0 }}</div>
          <div class="bg-[#243539] px-4 py-1 rounded-4xl text-xs">Voted</div>
        </div>
        <div class="max-w-[70px] h-auto">
          <img src={{ asset('icons/voted.png') }} alt="icon" width="70" height="70" />
        </div>
      </a>
    </div>

    {{-- Vote tally --}}
    <h3 class="mt-6 font-semibold text-lg px-11">Vote’s Tally</h3>

    @if(!$currentElection)
      <p class="flex justify-center items-center px-11 mt-2 text-sm text-gray-700 min-h-[55vh] text-center font-extrabold">
        Voting is not open right now, so vote tallies are not shown. They appear again while an election is actively accepting votes.
      </p>
    @endif

    <div class="mt-0.5 grid grid-cols-1 md:grid-cols-2 gap-6 p-11">
      @foreach($charts as $i => $cfg)
        <div class="rounded-[26px] bg-[#3f4347] text-white shadow overflow-hidden">
          <div class="px-4 py-2 border-b border-black/60 font-bold">
            {{ $cfg['position'] }}
          </div>

          <div class="p-4">
            <div class="h-[200px]">
              <canvas id="chart-{{ $i }}"></canvas>
            </div>
          </div>

          <div class="border-t border-black/60"></div>
        </div>
      @endforeach
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
  <script>
    const charts = @json($charts);

    charts.forEach((cfg, i) => {
      const ctx = document.getElementById('chart-' + i);

      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: cfg.labels,
          datasets: [{
            label: cfg.position,
            data: cfg.data,
            backgroundColor: '#d0352f',
            borderRadius: 4,
            barThickness: 36,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            datalabels: {
              anchor: 'end',
              align: 'end',
              color: '#ffffff',
              font: { weight: '700' }
            }
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { color: '#e5e7eb', font: { weight: '600' } }
            },
            y: {
              beginAtZero: true,
              grid: { color: 'rgba(0,0,0,.45)' },
              ticks: { display: false }
            }
          }
        },
        plugins: [ChartDataLabels]
      });
    });
  </script>
@endpush
