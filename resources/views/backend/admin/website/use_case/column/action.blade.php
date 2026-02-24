<ul class="d-flex gap-30 justify-content-end">
	@can('use-case.edit')
		<li>
			<a href="{{ route('use-case.edit',$usecase->id) }}"><i
						class="las la-edit"></i></a>
		</li>
	@endcan
	@can('use-case.destroy')
		<li>
			<a href="javascript:void(0)"
			   onclick="delete_row('{{ route('use-case.destroy', $usecase->id) }}')"
			   data-toggle="tooltip"
			   data-original-title="{{ __('delete') }}"><i class="las la-trash-alt"></i></a>
		</li>
	@endcan
</ul>
