<ul class="d-flex gap-30 justify-content-end">
	<li>
		<a class="__js_sync" href="{{ route('client.web.template.edit',$row->id) }}"
			data-id="{{ @$row->id }}"
			title="{{ __('edit') }}"><i class="las la-edit"></i></a>
	</li>
    <a href="javascript:void(0);" data-id="{{ $row->id }}"
        data-url="{{ route('client.web.template.delete', @$row->id) }}" class="text-danger __js_delete"
        data-bs-toggle="tooltip" aria-label="{{__('delete')}}" data-bs-original-title="{{__('delete')}}">
        <i class="las la-trash-alt"></i>
    </a>
</ul>