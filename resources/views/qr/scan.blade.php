{{-- resources/views/qr/scan.blade.php --}}
@extends('layouts.voter-auth')

@section('content')
<div class="max-w-xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">Scan Voter QR</h1>
  <div id="reader" class="w-full"></div>
  <div id="status" class="mt-3 text-sm text-gray-600"></div>
</div>
@endsection
