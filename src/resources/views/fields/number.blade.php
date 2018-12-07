<!-- number input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label class="col-md-3 control-label" for="{{ $field['name'] }}">{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <div class="col-md-9">
    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <div class="input-group-prepend"><span class="input-group-text">{!! $field['prefix'] !!}</span></div> @endif
        <input
        	type="number"
        	name="{{ $field['name'] }}"
            id="{{ $field['name'] }}"
            value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
            @include('crud::inc.field_attributes')
        	>
        @if(isset($field['suffix'])) <div class="input-group-append"><span class="input-group-text">{!! $field['suffix'] !!}</span></div> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="form-text">{!! $field['hint'] !!}</p>
    @endif
    </div>
</div>
