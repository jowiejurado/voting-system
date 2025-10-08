@php
  $hasElements = isset($elements) && !empty($elements);
@endphp

<nav role="navigation" aria-label="Pagination" class="inline-flex items-center gap-1">
  @if ($paginator->onFirstPage())
    <span class="px-3 py-1.5 text-sm rounded border border-gray-300 text-gray-400 cursor-not-allowed" aria-disabled="true">Previous</span>
  @else
    <a class="px-3 py-1.5 text-sm rounded border border-gray-300 hover:bg-gray-100"
       href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
  @endif

  @if ($hasElements)
    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="px-3 py-1.5 text-sm text-gray-500">{{ $element }}</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="px-3 py-1.5 text-sm rounded bg-black text-white">{{ $page }}</span>
          @else
            <a class="px-3 py-1.5 text-sm rounded border border-gray-300 hover:bg-gray-100"
               href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach
  @else
    <span class="px-3 py-1.5 text-sm rounded bg-black text-white">1</span>
  @endif

  @if ($paginator->hasMorePages())
    <a class="px-3 py-1.5 text-sm rounded border border-gray-300 hover:bg-gray-100"
       href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
  @else
    <span class="px-3 py-1.5 text-sm rounded border border-gray-300 text-gray-400 cursor-not-allowed" aria-disabled="true">Next</span>
  @endif
</nav>
