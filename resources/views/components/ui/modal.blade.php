@props([
  'id',
  'title' => null,
  'size' => 'max-w-[560px]',
  'form' => null,
  'closeButton' => true,
])

<div id="{{ $id }}"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-4"
     data-modal data-reset-on-close>
  <div class="w-full {{ $size }} rounded-2xl bg-white shadow-xl overflow-hidden">
    @if($title || $closeButton)
      <div class="flex items-center justify-between px-5 py-4 border-b">
        <h3 class="text-lg font-bold" data-modal-title>{{ $title }}</h3>
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
        class="px-5 py-4 space-y-4"
      >
        @csrf
        @if(!empty($form['spoof']))
          @method($form['spoof'])
        @endif

        {{ $slot }}

        <div class="flex items-center justify-end gap-2 pt-2">
          <button type="button" class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300" data-modal-cancel>
            Cancel
          </button>
          <button type="submit" class="px-4 py-2 rounded-md bg-black text-white" data-modal-submit>
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
