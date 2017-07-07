<?php
$entity_model = $crud->model;
$isRequired = isset($field['attributes']['required']) || (Schema::hasColumn($entity_model->getTable(), $field['name']) && !$entity_model::isColumnNullable($field['name']));
$dbdefault = method_exists($entity_model, 'getDefaultValue') ? $entity_model::getDefaultValue($field['name']) : '';
?>
<!-- text input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <input
    	type="email"
    	name="{{ $field['name'] }}"
        value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
        @include('crud::inc.field_attributes')
    	>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>
