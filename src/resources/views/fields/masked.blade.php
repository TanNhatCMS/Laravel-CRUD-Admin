<!-- text input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <div class="input-group-addon">{!! $field['prefix'] !!}</div> @endif
        <input
            id="masked_{{ $field['name'] }}"
            type="text"
            name="{{ $field['name'] }}"
            value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
            @include('crud::inc.field_attributes')
        >
        @if(isset($field['suffix'])) <div class="input-group-addon">{!! $field['suffix'] !!}</div> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields) && isset($field['mask']))
    @push('crud_fields_scripts')
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery-mask-plugin@1.14.15/dist/jquery.mask.min.js"></script>

        <script type="text/javascript">
            jQuery(document).ready(function($){
                $("#masked_{{ $field['name'] }}").mask('{{ $field['mask'] }}')
            });
        </script>

    @endpush
@endif
{{-- End of Extra CSS and JS --}}
