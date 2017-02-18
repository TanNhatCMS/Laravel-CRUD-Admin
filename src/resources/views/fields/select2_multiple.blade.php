<!-- select2 multiple -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <?php $entity_model = $crud->model; ?>
    <?php $owner_key = !empty($field['entity']) && method_exists($entity_model, $field['entity']) ? call_user_func([$entity_model, $field['entity']])->getOwnerKey() : null ?>
    <select
        name="{{ $field['name'] }}[]"
        @include('crud::inc.field_attributes', ['default_class' =>  'form-control select2'])
        multiple>

        @if (isset($field['model']))
            @foreach ($field['model']::all() as $connected_entity_entry)
                <?php $primaryKeyName = $owner_key === null ? $connected_entity_entry->getKeyName() : $owner_key ?>
                <?php $value = $owner_key === null ? $connected_entity_entry->getKey() : $connected_entity_entry->{$owner_key} ?>
                <option value="{{ $value }}"
                    @if ( (isset($field['value']) && in_array($value, $field['value']->pluck($primaryKeyName, $primaryKeyName)->toArray())) || ( old( $field["name"] ) && in_array($value, old( $field["name"])) ) )
                         selected
                    @endif
                >{{ $connected_entity_entry->{$field['attribute']} }}</option>
            @endforeach
        @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <!-- include select2 css-->
        <link href="{{ asset('vendor/backpack/select2/select2.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('vendor/backpack/select2/select2-bootstrap-dick.css') }}" rel="stylesheet" type="text/css" />
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- include select2 js-->
        <script src="{{ asset('vendor/backpack/select2/select2.js') }}"></script>
        <script>
            jQuery(document).ready(function($) {
                // trigger select2 for each untriggered select2_multiple box
                $('.select2').each(function (i, obj) {
                    if (!$(obj).data("select2"))
                    {
                        $(obj).select2();
                    }
                });
            });
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
