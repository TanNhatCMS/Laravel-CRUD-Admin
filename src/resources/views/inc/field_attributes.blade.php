@if (isset($field['attributes']))
    @foreach ($field['attributes'] as $attribute => $value)
    	@if (is_string($attribute))
        {{ $attribute }}="{{ $value }}"
        @endif
    @endforeach

    @if (!isset($field['attributes']['class']))
    	@if (isset($default_class))
    		class="{{ $default_class }}"
    	@else
    		class="form-control"
    	@endif
    @endif
@else
	@if (isset($default_class))
		class="{{ $default_class }}"
	@else
		class="form-control"
	@endif
@endif
@if (isset($field['unique']) && $field['unique'])
    @php
    $unique_config = [
        'field_name' => $field['name'],
        'display_name' => isset($field['unique_hint']) ? $field['unique_hint'] : false,
        'hint' => isset($field['unique_hint']) ? true : false
    ];
    @endphp
    data-unique="{{ json_encode($unique_config) }}"
@endif
