@props([
  'id',
  'title' => null,
  'size' => 'max-w-[800px]',
  'form' => null,
  'closeButton' => true,
])

<div id="{{ $id }}"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-3 sm:p-4 border overflow-y-auto"
     data-modal data-reset-on-close>
  <div class="w-full max-h-[min(90dvh,900px)] my-auto {{ $size }} rounded-2xl bg-white shadow-xl overflow-hidden flex flex-col">
    @if($title || $closeButton)
      <div class="flex items-center justify-between gap-2 px-4 sm:px-5 py-3 sm:py-4 border-b shrink-0 min-w-0">
        <h3 class="text-base sm:text-lg font-bold truncate min-w-0 pr-2" data-modal-title>{{ $title }}</h3>
        @if($closeButton)
          <button type="button" class="text-gray-500 hover:text-gray-700" data-modal-close>&times;</button>
        @endif
      </div>
    @endif

    @if($form)
      <form
        id="{{ $form['id'] ?? ($id.'-form') }}"
        method="post"
        action="{{ $form['action'] ?? '' }}"
        class="flex flex-col min-h-0 flex-1 px-4 sm:px-5 py-4 space-y-4 overflow-y-auto max-h-[min(75dvh,700px)] sm:max-h-none"
      >
        @csrf
        @if(!empty($form['spoof']))
          @method($form['spoof'])
        @endif

        {{ $slot }}

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-2 shrink-0">
          <button type="button" class="w-full sm:w-auto px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300" data-modal-cancel>
            Cancel
          </button>
          <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-md bg-black text-white" data-modal-submit>
            {{ $form['submitText'] ?? 'Submit' }}
          </button>
        </div>
      </form>
    @else
      <div class="px-5 py-4">
        {{ $slot }}
      </div>
    @endif
  </div>
</div>
