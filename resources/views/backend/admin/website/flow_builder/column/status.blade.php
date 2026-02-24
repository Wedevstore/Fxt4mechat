<div class="setting-check">
    <input type="checkbox" class="status-change"
           {{ ($flowbuilder->status == 1) ? 'checked' : '' }} data-id="{{ $flowbuilder->id }}" value="website-flow-builder-status/{{$flowbuilder->id}}"
           id="customSwitch2-{{$flowbuilder->id}}">
    <label for="customSwitch2-{{ $flowbuilder->id }}"></label>
</div>
