<!-- dependencyJson -->
<div class="form-group col-md-12 checklist_dependency"  data-entity ="{{ $field['field_unique_name'] }}" @include('crud::inc.field_wrapper_attributes')>
    @include('crud::inc.field_translatable_icon')
    <?php
        $entity_model = $crud->getModel();

        //short name for dependency fields
        $primary_dependency = $field['subfields']['primary'];
        $secondary_dependency = $field['subfields']['secondary'];


        //all items with relation
        $dependencies = $primary_dependency['model']::with($primary_dependency['entity_secondary'])->get();

        $dependencyArray = [];

        //convert dependency array to simple matrix ( prymary id as key and array with secondaries id )
        foreach ($dependencies as $primary) {
            $dependencyArray[$primary->id] = [];
            foreach ($primary->{$primary_dependency['entity_secondary']} as $secondary) {
                $dependencyArray[$primary->id][] = $secondary->id;
            }
        }

      //for update form, get initial state of the entity
      if( isset($id) && $id ){

        //get entity with relations for primary dependency
        $entity_dependencies = $entity_model->with($primary_dependency['entity'])
          ->with($primary_dependency['entity'].'.'.$primary_dependency['entity_secondary'])
          ->find($id);

            $secondaries_from_primary = [];

            //convert relation in array
            $primary_array = $entity_dependencies->{$primary_dependency['entity']}->toArray();

            $secondary_ids = [];

            //create secondary dependency from primary relation, used to check what chekbox must be check from second checklist
            if (old($primary_dependency['name'])) {
                foreach (old($primary_dependency['name']) as $primary_item) {
                    foreach ($dependencyArray[$primary_item] as $second_item) {
                        $secondary_ids[$second_item] = $second_item;
                    }
                }
            } else { //create dependecies from relation if not from validate error
                foreach ($primary_array as $primary_item) {
                    foreach ($primary_item[$secondary_dependency['entity']] as $second_item) {
                        $secondary_ids[$second_item['id']] = $second_item['id'];
                    }
                }
            }
        }

        //json encode of dependency matrix
        $dependencyJson = json_encode($dependencyArray);
    ?>

    <div class="row" >
        <div class="col-xs-12">
            <label>{!! $primary_dependency['label'] !!}</label>
            @foreach ($primary_dependency['model']::all() as $connected_entity_entry)
                @if( ( isset($field['value']) && is_array($field['value']) && in_array($connected_entity_entry->id, $field['value'][0]->pluck('id', 'id')->toArray())) || ( old($primary_dependency["name"]) && in_array($connected_entity_entry->id, old( $primary_dependency["name"])) ) )
                    <p>{{ $connected_entity_entry->{$primary_dependency['attribute']} }}</p>
                @endif
            @endforeach
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <label>{!! $secondary_dependency['label'] !!}</label>
            @foreach ($secondary_dependency['model']::all() as $connected_entity_entry)
                @if( ( isset($field['value']) && is_array($field['value']) && (  in_array($connected_entity_entry->id, $field['value'][1]->pluck('id', 'id')->toArray()) || isset( $secondary_ids[$connected_entity_entry->id])) || ( old($secondary_dependency['name']) &&   in_array($connected_entity_entry->id, old($secondary_dependency['name'])) )))
                    {{ $connected_entity_entry->{$secondary_dependency['attribute']} }}
                @endif
            @endforeach
        </div>
    </div>
  </div>
