<div class="space-y-3" {{ $attributes }}>
  <h4 class="text-base font-bold pt-1">Admin Authentication</h4>

  <div>
    <label class="block text-sm mb-1">Admin ID</label>
    <input type="text" name="admin_id"
           class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
           required autocomplete="off" autocapitalize="none" autocorrect="off" spellcheck="false"
           data-clear-on-close>
    @error('admin_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm mb-1">Password</label>
    <input type="password" name="password"
           class="w-full border-2 border-gray-400 py-2 px-3 outline-none"
           required autocomplete="new-password"
           data-clear-on-close>
    @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>
</div>
