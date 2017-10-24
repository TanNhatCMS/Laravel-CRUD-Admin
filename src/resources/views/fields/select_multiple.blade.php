<!-- select multiple -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <?php $entity_model = $crud->model; ?>
    <?php $owner_key = !empty($field['entity']) && method_exists($entity_model, $field['entity']) ? call_user_func([$entity_model, $field['entity']])->getOwnerKey() : null ?>
    <select
    	class="form-control"
        name="{{ $field['name'] }}[]"
        @include('crud::inc.field_attributes')
    	multiple>

		@if (!isset($field['allows_null']) || $field['allows_null'])
			<option value="">-</option>
		@endif

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