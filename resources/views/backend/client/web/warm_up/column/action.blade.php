<div class="d-flex align-items-center gap-2">
    <div>
        <a href="{{ route('client.web.whatsapp.warm-up.manage',$warmup->id) }}" class="d-flex align-items-center btn sg-btn-primary gap-2">
            <i class="las la-chart-bar"></i>
            <span>{{__('manage')}}</span>
        </a>
    </div>

    <div>
        <ul class="d-flex gap-10 justify-content-center ">
            <li><a href="{{ route('client.web.whatsapp.warm-up.edit', $warmup->id) }}"><i class="las la-edit" title="{{__('edit')}}"></i></a></li>

            <li><a onclick="delete_row('{{ route('client.web.whatsapp.warm-up.delete', $warmup->id) }}')"
                    href="javascript:void(0)"><i class="las la-trash-alt" title="{{__('delete')}}"></i></a></li>
        </ul>
    </div>
</div>

