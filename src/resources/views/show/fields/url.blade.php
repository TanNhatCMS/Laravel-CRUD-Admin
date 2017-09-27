<!-- html5 url input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    <p>
        <a href="{{ $field['value'] }}" target="_blank">{{ $field['value'] }}</a>
    </p>
</div>