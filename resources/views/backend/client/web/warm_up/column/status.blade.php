<div class="setting-check">
    <input 
        type="checkbox" 
        class="warm-up-status-change" 
        id="customSwitch2-{{ $warmup->id }}"
        data-id="{{ $warmup->id }}"
        {{ $warmup->status == 1 ? 'checked' : '' }}
    >
    <label for="customSwitch2-{{ $warmup->id }}"></label>
</div>
