<!-- select2 -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <?php $entity_model = $crud->model; ?>
    @if (isset($field['model']))
        @foreach ($field['model']::all() as $connected_entity_entry)
            @if ( ( old($field['name']) && old($field['name']) == $connected_entity_entry->getKey() ) || (isset($field['value']) && $connected_entity_entry->getKey()==$field['value']))
                <p>{{ $connected_entity_entry->{$field['attribute']} }}</p>
            @endif
        @endforeach
    @endif
</div>