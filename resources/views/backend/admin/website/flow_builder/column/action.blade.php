<ul class="d-flex gap-30 justify-content-end">
	@can('flow-builder.edit')
		<li>
			<a href="{{ route('flow-builder.edit',$flowbuilder->id) }}"><i
						class="las la-edit"></i></a>
		</li>
	@endcan
	{{-- @can('flow-builder.destroy')
		<li>
			<a href="javascript:void(0)"
			   onclick="delete_row('{{ route('flow-builder.destroy', $flowbuilder->id) }}')"
			   data-toggle="tooltip"
			   data-original-title="{{ __('delete') }}"><i class="las la-trash-alt"></i></a>
		</li>
	@endcan --}}
</ul>
