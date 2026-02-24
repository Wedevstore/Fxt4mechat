<div class="setting-check">
	<input type="checkbox" class="status-change"
	       {{ ($q->status == 1) ? 'checked' : '' }} data-id="{{$q->id}}" value="contact-status/{{$q->id}}"
	       id="customSwitch2-{{$q->id}}">
	<label for="customSwitch2-{{ $q->id }}"></label>
</div>