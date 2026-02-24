<div class="setting-check">
    <input type="checkbox" class="status-change"
           {{ ($highlightedfeature->status == 1) ? 'checked' : '' }} data-id="{{ $highlightedfeature->id }}" value="website-highlighted-feature-status/{{$highlightedfeature->id}}"
           id="customSwitch2-{{$highlightedfeature->id}}">
    <label for="customSwitch2-{{ $highlightedfeature->id }}"></label>
</div>
