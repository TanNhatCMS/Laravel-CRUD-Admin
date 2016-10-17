<?php
    $entity_model = $crud->getModel();
 	
 	//for update form, get initial state of the entity
    if( isset($id) && $id ){
    	$entity_column = $entity_model::find($id)->getAttributes();
	}
?>

<div @include('crud::inc.field_wrapper_attributes')>
    <label>{!! $field['label'] !!}</label>       
    <input 
      	type="text" 
      	name="{{$field['name']}}" 
      	id="{{$field['name']}}"
      	@include('crud::inc.field_attributes')   
    >
   
    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

@foreach ($field['components'] as $attribute)

	<div @include('crud::inc.field_wrapper_attributes') >
	    <label>{!! $attribute['label'] !!}</label>
	    <input 
	      	type="text" 
	      	name="{{$attribute['name']}}"
	      	id="{{$attribute['name']}}"
	      	value="{{ old($attribute['name'], isset($entity_column[$attribute['name']]) ? $entity_column[$attribute['name']] : null) }}"
	      	readonly
	      	@include('crud::inc.field_attributes')
	    >
	</div>

@endforeach

{{-- Note: you can use  to only load some CSS/JS once, even though there are multiple instances of it --}}

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    
    {{-- @push('crud_fields_styles')
        <!-- no styles -->
    @endpush --}}

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script>

			var field = <?php echo json_encode($field); ?>;

			function initAutocomplete() {
  
			 	if(document.getElementById(field.name)){
			    	var autocomplete = new google.maps.places.Autocomplete((document.getElementById(field.name)),{types: ['address']});
			    	autocomplete.addListener('place_changed', function(){fillInAddress(autocomplete)});
			  	}
			}

			function fillInAddress(autocomplete) {
			  	// Get the place details from the autocomplete object.
			 	var place = autocomplete.getPlace();
			   	var val = [];
			  	
			  	// Get each component of the address from the place details
			  	for (var i = 0; i < place.address_components.length; i++) {
			    	var addressType = place.address_components[i].types[0];
			    	val[addressType] = place.address_components[i];
			  	}
				
				// Fill the corresponding field on the form if it exists.
			  	for (var component in field.components) {
			    	document.getElementById(field.components[component].name).readOnly = false;
			    	if (val[component]){
			    		document.getElementById(field.components[component].name).value = val[component][field.components[component].type];	
			    	} else {
			    		document.getElementById(field.components[component].name).value = '';
			    	}
			  	}
			}

        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY')}}&libraries=places&callback=initAutocomplete" async defer></script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}







