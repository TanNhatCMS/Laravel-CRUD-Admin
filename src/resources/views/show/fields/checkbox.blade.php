<!-- checkbox field -->

<div @include('crud::inc.field_wrapper_attributes') >
        <label>{!! $field['label'] !!}</label>

        @if((int) $field['value'] == 1)
            <p>True</p>
        @else
            <p>False</p>
        @endif
</div>
