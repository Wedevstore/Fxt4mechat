<ul class="d-flex gap-30 justify-content-end">
	@can('small-title.edit')
		<li>
			<a href="{{ route('small-title.edit',$smalltitle->id) }}"><i
						class="las la-edit"></i></a>
		</li>
	@endcan
	{{-- @can('small-title.destroy')
		<li>
			<a href="javascript:void(0)"
			   onclick="delete_row('{{ route('small-title.destroy', $smalltitle->id) }}')"
			   data-toggle="tooltip"
			   data-original-title="{{ __('delete') }}"><i class="las la-trash-alt"></i></a>
		</li>
	@endcan --}}
</ul>
