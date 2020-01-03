<!-- multiple_tags_input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    <select multiple
            data-role="tagsinput"
            name="{{ $field['name'] }}"
            data-init-function="bpFieldInitMultipleTagsInput"
    >
    </select>

    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    @push('crud_fields_styles')
        <style>
            .bootstrap-tagsinput input {
                border: none;
                box-shadow: none;
                outline: none;
                background-color: transparent;
                padding: 0 6px;
                margin: 0;
                width: auto;
                max-width: inherit;
            }
        </style>
    @endpush

    @push('crud_fields_scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
        <script>
            function bpFieldInitMultipleTagsInput(element) {
                if (!element.prev().hasClass('form-control')) {
                    element.prev().addClass('form-control');
                }
            }
        </script>
    @endpush
@endif
