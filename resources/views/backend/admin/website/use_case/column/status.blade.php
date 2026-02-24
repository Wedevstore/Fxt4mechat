<div class="setting-check">
    <input type="checkbox" class="status-change"
           {{ ($usecase->status == 1) ? 'checked' : '' }} data-id="{{ $usecase->id }}" value="website-use-case-status/{{$usecase->id}}"
           id="customSwitch2-{{$usecase->id}}">
    <label for="customSwitch2-{{ $usecase->id }}"></label>
</div>
