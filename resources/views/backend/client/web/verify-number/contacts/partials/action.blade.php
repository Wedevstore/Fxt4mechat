<ul class="d-flex gap-30 justify-content-end">
	@php
	$sms_marketing = addon_is_activated('sms_marketing');
	@endphp
	@if ($sms_marketing)
	<li>
		<a href="javascript:void(0);" data-id="{{ @$q->id }}" title="{{ __('send_sms') }}" class="__sms_send_modal">
			<i class="las la-sms"></i>
		</a>
	</li>
	@endif

	@if (
		$q->last_conversation_at &&
		\Carbon\Carbon::parse($q->last_conversation_at)->gte(\Carbon\Carbon::now()->subHours(24))
	)
	<li>
		<a href="{{ route('client.chat.index', ['contact' => $q->id]) }}" data-id="{{ $q->id }}" title="{{ __('chat') }}">
			<i class="la la-whatsapp"></i>
		</a>
	</li>
	@endif

	<li>
		<a href="javascript:void(0);" id="__view_details" data-id="{{ @$q->id }}" title="{{ __("view_details") }}" class="__view_details">
			<i class="las la-link"></i>
		</a>
	</li>
	<li>
		<a href="javascript:void(0);" data-id="{{ @$q->id }}" title="{{ __("send_template") }}" class="__template_modal">
			<i class="las la-comment"></i>
		</a>
	</li>

	<li>
		<a href="{{ route('client.contact.edit', @$q->id) }}" title="{{ __("edit") }}">
			<i class="las la-edit"></i>
		</a>
	</li>

	<li>
		@if($q->is_blacklist == 0)
		<a href="javascript:void(0);"
		   onclick="contact('{{ url('client/contact/add-blacklist/') }}', {{ $q->id }}, false)" title="{{ __('add-blacklist') }}">
			<i class="las la-ban"></i>
		</a>
		@else
		<a href="javascript:void(0);"
		   onclick="contact('{{ url('client/contact/remove-blacklist/') }}', {{ $q->id }}, false)" title="{{ __("remove-blacklist") }}">
			<i class="las la-check-circle"></i>
		</a>
		@endif
	</li>

	<li>
		<a data-url="{{ route('client.contact.delete', $q->id) }}"
		   href="javascript:void(0)" title="{{ __('delete') }}" class="__js_delete">
			<i class="las la-trash-alt"></i>
		</a>
	</li>
</ul>
