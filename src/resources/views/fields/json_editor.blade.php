{{-- json field based on: https://github.com/josdejong/jsoneditor --}}
@php

$value = new stdClass();

if (old($field['name'])) {
    $value = old($field['name']);
} elseif (isset($field['value']) && isset($field['default'])) {
    $value = array_merge_recursive($field['default'], $field['value']);
} elseif (isset($field['value'])) {
    $value = $field['value'];
} elseif (isset($field['default'])) {
    $value = $field['default'];
}

// if attribute casting is used, convert to JSON
if (is_array($value) || is_object($value) ) {
    $value = json_encode($value);
} elseif ($value instanceof \Spatie\SchemalessAttributes\SchemalessAttributes) {
    $value = json_encode($value->all());
}
@endphp

<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>

    <div id="jsoneditor" style="height: 400px;"></div>

    <input type="hidden" id="{{ $field['name'] }}"
           name="{{ $field['name'] }}"
           value=""
            @include('crud::inc.field_attributes') />

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))
    {{-- FIELD EXTRA CSS  --}}
    {{-- push things in the after_styles section --}}

    @push('crud_fields_styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/5.24.6/jsoneditor.min.css" />
    @endpush


    {{-- FIELD EXTRA JS --}}
    {{-- push things in the after_scripts section --}}

    @push('crud_fields_scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/5.24.6/jsoneditor.min.js"></script>
        @javascript('jsonString', $value)
        <script>
            const container = document.getElementById('jsoneditor');

            const options = {
                onChange: function() {
                    const hiddenField = document.getElementById('{{ $field['name'] }}');
                    hiddenField.value = editor.getText();
                },
                modes: ['form', 'tree', 'code']
            };

            const editor = new JSONEditor(container, options, JSON.parse(jsonString));
            document.getElementById('{{ $field['name'] }}').value = editor.getText();
        </script>
    @endpush
@endif
{{-- Note: most of the times you'll want to use @if ($crud->checkIfFieldIsFirstOfItsType($field, $fields)) to only load CSS/JS once, even though there are multiple instances of it. --}}
