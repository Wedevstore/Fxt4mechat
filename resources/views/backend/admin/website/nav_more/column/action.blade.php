<ul class="d-flex gap-30 justify-content-end">
	@can('nav-more.edit')
		<li>
			<a href="{{ route('nav-more.edit',$navmore->id) }}"><i
						class="las la-edit"></i></a>
		</li>
	@endcan
	@can('nav-more.destroy')
		<li>
			<a href="javascript:void(0)"
			   onclick="delete_row('{{ route('nav-more.destroy', $navmore->id) }}')"
			   data-toggle="tooltip"
			   data-original-title="{{ __('delete') }}"><i class="las la-trash-alt"></i></a>
		</li>
	@endcan
</ul>
