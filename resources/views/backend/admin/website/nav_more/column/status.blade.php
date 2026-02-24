<div class="setting-check">
    <input type="checkbox" class="status-change"
           {{ ($navmore->status == 1) ? 'checked' : '' }} data-id="{{ $navmore->id }}" value="website-nav-more-status/{{$navmore->id}}"
           id="customSwitch2-{{$navmore->id}}">
    <label for="customSwitch2-{{ $navmore->id }}"></label>
</div>
