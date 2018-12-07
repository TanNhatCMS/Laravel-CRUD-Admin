<!-- html5 url input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label class="col-md-3 control-label">{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <div class="col-md-9">
    <input
    	type="url"
    	name="{{ $field['name'] }}"
        value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
        @include('crud::inc.field_attributes')
    	>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
    </div>
</div>