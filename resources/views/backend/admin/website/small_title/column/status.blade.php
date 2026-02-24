<div class="setting-check">
    <input type="checkbox" class="status-change"
           {{ ($smalltitle->status == 1) ? 'checked' : '' }} data-id="{{ $smalltitle->id }}" value="website-small-title-status/{{$smalltitle->id}}"
           id="customSwitch2-{{$smalltitle->id}}">
    <label for="customSwitch2-{{ $smalltitle->id }}"></label>
</div>
