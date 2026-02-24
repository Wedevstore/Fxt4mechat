<ul class="d-flex gap-30 justify-content-end">
	@can('highlighted-feature.edit')
		<li>
			<a href="{{ route('highlighted-feature.edit',$highlightedfeature->id) }}"><i
						class="las la-edit"></i></a>
		</li>
	@endcan
	@can('highlighted-feature.destroy')
		<li>
			<a href="javascript:void(0)"
			   onclick="delete_row('{{ route('highlighted-feature.destroy', $highlightedfeature->id) }}')"
			   data-toggle="tooltip"
			   data-original-title="{{ __('delete') }}"><i class="las la-trash-alt"></i></a>
		</li>
	@endcan
</ul>
