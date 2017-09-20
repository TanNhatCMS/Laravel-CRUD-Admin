<!-- select multiple -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
	@if (isset($field['model']))
		@foreach ($field['model']::all() as $connected_entity_entry)
			@if ( (isset($field['value']) && in_array($connected_entity_entry->getKey(), $field['value']->pluck($connected_entity_entry->getKeyName(), $connected_entity_entry->getKeyName())->toArray())) || ( old( $field["name"] ) && in_array($connected_entity_entry->getKey(), old( $field["name"])) ) )
				<p>{{ $connected_entity_entry->{$field['attribute']} }}</p>
			@endif
		@endforeach
	@endif
</div>