<!-- send newsletter button -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    <div>
        <a href="{{ url($crud->route . '/' . $entry->getKey() . '/send') }}" class="btn btn-primary ladda-button" data-style="zoom-in"><span class="ladda-label"><i class="fa fa-check"></i> Send</span></a>
    </div>
</div>