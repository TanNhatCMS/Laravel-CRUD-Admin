<!-- textarea -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label class="col-md-3 control-label">{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <div class="col-md-9">
        <textarea
            name="{{ $field['name'] }}"
            @include('crud::inc.field_attributes')

            >{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}</textarea>

        {{-- HINT --}}
        @if (isset($field['hint']))
            <p class="form-text">{!! $field['hint'] !!}</p>
        @endif
    </div>
</div>