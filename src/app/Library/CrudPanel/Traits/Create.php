<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait Create
{
    /*
    |--------------------------------------------------------------------------
    |                                   CREATE
    |--------------------------------------------------------------------------
    */

    /**
     * Insert a row in the database.
     *
     * @param  array  $input  All input values to be inserted.
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($input)
    {
        $input = $this->decodeJsonCastedAttributes($input);
        $input = $this->compactFakeFields($input);

        $input = $this->changeBelongsToNamesFromRelationshipToForeignKey($input);

        $field_names_to_exclude = $this->getFieldNamesBeforeFirstDot($this->getRelationFieldsWithoutRelationType('BelongsTo', true));

        $item = $this->model->create(Arr::except($input, $field_names_to_exclude));

        $relation_input = $this->getRelationDetailsFromInput($input);
        
        // handle the creation of the model relations after the main entity is created.
        $this->createRelationsForItem($item, $relation_input);

        return $item;
    }

    /**
     * Get all fields needed for the ADD NEW ENTRY form.
     *
     * @return array The fields with attributes and fake attributes.
     */
    public function getCreateFields()
    {
        return $this->fields();
    }

    /**
     * Get all fields with relation set (model key set on field).
     *
     * @return array The fields with model key set.
     */
    public function getRelationFields()
    {
        $fields = $this->getCleanStateFields();
        $relationFields = [];

        foreach ($fields as $field) {
            if (isset($field['model']) && $field['model'] !== false) {
                array_push($relationFields, $field);
            }

            // if a field has an array name AND subfields
            // then take those fields into account (check if they have relationships);
            // this is done in particular for the checklist_dependency field,
            // but other fields could use it too, in the future;
            if (is_array($field['name']) &&
                isset($field['subfields']) &&
                is_array($field['subfields']) &&
                count($field['subfields'])) {
                foreach ($field['subfields'] as $subfield) {
                    if (isset($subfield['model']) && $subfield['model'] !== false) {
                        array_push($relationFields, $subfield);
                    }
                }
            }
        }
        return $relationFields; 
    }

    /**
     * Create relations for the provided model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $item  The current CRUD model.
     * @param  array  $formattedRelations  The form data.
     * @return bool|null
     */
    private function createRelationsForItem($item, $formattedRelations)
    {
        if (! isset($formattedRelations['relations'])) {
            return false;
        }
        foreach ($formattedRelations['relations'] as $relationMethod => $relationDetails) {
            if (! isset($relationDetails['model'])) {
                continue;
            }
            $relation = $item->{$relationMethod}();
            $relationType = $relationDetails['relation_type'];

            switch ($relationType) {
                case 'HasOne':
                case 'MorphOne':
                        if (is_null($relationDetails['values'])) {
                            $relation->delete();
                            break;
                        }
                
                        $modelInstance = $relation->updateOrCreate([], $relationDetails['values']);
                       
                    break;
                case 'HasMany':
                case 'MorphMany':
                    $relationValues = $relationDetails['values'];
                    // if relation values are null we can only attach, also we check if we sent
                    // - a single dimensional array: [1,2,3]
                    // - an array of arrays: [[1][2][3]]
                    // if is as single dimensional array we can only attach.
                    if ($relationValues === null || ! is_multidimensional_array($relationValues)) {
                        $this->attachManyRelation($item, $relation, $relationDetails, $relationValues);
                    } else {
                        $this->createManyEntries($item, $relation, $relationMethod, $relationDetails);
                    }
                    break;
                case 'BelongsToMany':
                case 'MorphToMany':
                    $values = $relationDetails['values'] ?? [];
                    $values = is_string($values) ? json_decode($values, true) : $values;
                    $relationValues = [];

                    if (is_multidimensional_array($values)) {
                        foreach ($values as $value) {
                            if(isset($value[$relationMethod])) {
                                $relationValues[$value[$relationMethod]] = Arr::except($value, $relationMethod);
                            }
                        }
                    }

                    // if there is no relation data, and the values array is single dimensional we have
                    // an array of keys with no aditional pivot data. sync those.
                    if (empty($relationValues) && !is_multidimensional_array($values)) {
                        $relationValues = array_values($values);
                    }

                    $item->{$relationMethod}()->sync($relationValues);
                    break;
            }

            if (isset($relationDetails['relations'])) {
                $this->createRelationsForItem($modelInstance, ['relations' => $relationDetails['relations']]);
            }
        }
    }

    /**
     * When using the HasMany/MorphMany relations as selectable elements we use this function to "mimic-sync" in those relations.
     * Since HasMany/MorphMany does not have the `sync` method, we manually re-create it.
     * Here we add the entries that developer added and remove the ones that are not in the list.
     * This removal process happens with the following rules:
     * - by default Backpack will behave like a `sync` from M-M relations: it deletes previous entries and add only the current ones.
     * - `force_delete` is configurable in the field, it's `true` by default. When false, if connecting column is nullable instead of deleting the row we set the column to null.
     * - `fallback_id` could be provided. In this case instead of deleting we set the connecting key to whatever developer gives us.
     *
     * @return mixed
     */
    private function attachManyRelation($item, $relation, $relationDetails, $relation_values)
    {
        $modelInstance = $relation->getRelated();
        $relationForeignKey = $relation->getForeignKeyName();
        $relationLocalKey = $relation->getLocalKeyName();

        if ($relation_values === null) {
            // the developer cleared the selection
            // we gonna clear all related values by setting up the value to the fallback id, to null or delete.
            $removed_entries = $modelInstance->where($relationForeignKey, $item->{$relationLocalKey});

            return $this->handleManyRelationItemRemoval($modelInstance, $removed_entries, $relationDetails, $relationForeignKey);
        }
        // we add the new values into the relation, if it is HasMany we only update the foreign_key,
        // otherwise (it's a MorphMany) we need to update the morphs keys too
        $toUpdate[$relationForeignKey] = $item->{$relationLocalKey};

        if($relationDetails['relation_type'] === 'MorphMany') {
            $toUpdate[$relation->getQualifiedMorphType()] = $relation->getMorphClass();
        }

        $modelInstance->whereIn($modelInstance->getKeyName(), $relation_values)
            ->update($toUpdate);

        // we clear up any values that were removed from model relation.
        // if developer provided a fallback id, we use it
        // if column is nullable we set it to null if developer didn't specify `force_delete => true`
        // if none of the above we delete the model from database
        $removed_entries = $modelInstance->whereNotIn($modelInstance->getKeyName(), $relation_values)
                            ->where($relationForeignKey, $item->{$relationLocalKey});
        
        // if relation is MorphMany we also match by morph type.                    
        if($relationDetails['relation_type'] === 'MorphMany') {
            $removed_entries->where($relation->getQualifiedMorphType(), $relation->getMorphClass());
        }

        return $this->handleManyRelationItemRemoval($modelInstance, $removed_entries, $relationDetails, $relationForeignKey);
    }

    private function handleManyRelationItemRemoval($model_instance, $removed_entries, $relationDetails, $relation_foreign_key)
    {
        $relation_column_is_nullable = $model_instance->isColumnNullable($relation_foreign_key);
        $force_delete = $relationDetails['force_delete'] ?? false;
        $fallback_id = $relationDetails['fallback_id'] ?? false;

        if ($fallback_id) {
            return $removed_entries->update([$relation_foreign_key => $fallback_id]);
        }

        if ($force_delete) {
            return $removed_entries->delete();
        }

        if (! $relation_column_is_nullable && $model_instance->dbColumnHasDefault($relation_foreign_key)) {
            return $removed_entries->update([$relation_foreign_key => $model_instance->getDbColumnDefault($relation_foreign_key)]);
        }

        return $removed_entries->update([$relation_foreign_key => null]);
    }

    /**
     * Handle HasMany/MorphMany relations when used as creatable entries in the crud.
     * By using repeatable field, developer can allow the creation of such entries
     * in the crud forms.
     *
     * @return void
     */
    private function createManyEntries($entry, $relation, $relationMethod, $relationDetails)
    {

        
        $items = $relationDetails['values'];

        $relation_local_key = $relation->getLocalKeyName();

        $created_ids = [];

        foreach ($items as $item) {
            if (isset($item[$relation_local_key]) && ! empty($item[$relation_local_key])) {
                $entry->{$relationMethod}()->where($relation_local_key, $item[$relation_local_key])->update(Arr::except($item, $relation_local_key));
            } else {
                $created_ids[] = $entry->{$relationMethod}()->create($item)->{$relation_local_key};
            }
        }

        // get from $items the sent ids, and merge the ones created.
        $relatedItemsSent = array_merge(array_filter(Arr::pluck($items, $relation_local_key)), $created_ids);

        if (! empty($relatedItemsSent)) {
            // we perform the cleanup of removed database items
            $entry->{$relationMethod}()->whereNotIn($relation_local_key, $relatedItemsSent)->delete();
        }
    }

    /**
     * Get a relation data array from the form data. For each relation defined in the fields
     * through the entity attribute, set the model, parent model and attribute values.
     *
     * We traverse this relation array later to create the relations, for example:
     * - Current model HasOne Address
     * - Address (line_1, country_id) BelongsTo Country through country_id in Address Model
     *
     * So when editing current model crud user have two fields
     * - address.line_1
     * - address.country
     * (we infer country_id from relation)
     *
     * Those will be nested accordingly in this relation array, so address relation
     * will have a nested relation with country.
     *
     * @param  array  $input  The form input.
     * @return array The formatted relation details.
     */
    private function getRelationDetailsFromInput($input)
    {

        $relationFields = $this->getRelationFields();
        
        // exclude the already attached belongs to relations in the main entry but include nested belongs to.
        $relationFields = Arr::where($relationFields, function ($field, $key) {
            return $field['relation_type'] !== 'BelongsTo' || ($field['relation_type'] === 'BelongsTo' && Str::contains($field['name'], '.'));
        });

        //remove fields that are not in the submitted form input
        $relationFields = array_filter($relationFields, function ($field) use ($input) {
            return Arr::has($input, $field['name']);
        });
        
        $relationDetails = [];
        foreach ($relationFields as $field) {
            $relationDetails = $this->geFieldDetailsForRelationSaving($field, $input, $relationDetails);

            if(isset($field['subfields'])) {
                foreach($field['subfields'] as $subfield) {
                    $subfield['baseModel'] = $field['model'];
                    $subfield['baseEntity'] = $field['entity'];
                    $subfield = $this->makeSureFieldHasNecessaryAttributes($subfield);
                    if(isset($subfield['relation_type'])) {
                        $relationDetails = $this->geFieldDetailsForRelationSaving($subfield, $input, $relationDetails, $field);
                    }
                }
            }
        }
        return $relationDetails;
    }

    private function geFieldDetailsForRelationSaving($field, $input, $relationDetails, $parent = false) {
            // we split the entity into relations, eg: user.accountDetails.address
            // (user -> HasOne accountDetails -> BelongsTo address)
            // we specifically use only the relation entity because relations like
            // HasOne and MorphOne use the attribute in the relation string
            
            $relationEntity = $field['entity'];
            $attributeName = (string) Str::of($field['name'])->afterLast('.');
           
            if(!$parent) {
                $key = implode('.relations.', explode('.', $this->getOnlyRelationEntity($field)));
                $fieldDetails = Arr::get($relationDetails, 'relations.'.$key, []);
                if($field['relation_type'] === 'HasOne' || $field['relation_type'] === 'MorphOne') {
                    if(!isset($field['subfields'])) {
                        $fieldDetails['values'][$attributeName] = is_array(Arr::get($input, $relationEntity)) ? current(Arr::get($input, $relationEntity)) : Arr::get($input, $relationEntity);
                    }else{
                        
                        $fieldDetails['values'] = is_array(Arr::get($input, $relationEntity)) ? current(Arr::get($input, $relationEntity)) : Arr::get($input, $relationEntity);
                    }
                }elseif($field['relation_type'] === 'BelongsTo') {
                    $relation = $this->getRelationInstance(['entity' => $field['entity']]);
                    $belongsToKey = $field['name'];
                    if(Str::contains($field['name'], '.')) {
                        $belongsToKey = Str::afterLast($field['name'], '.');
                    }
                    if($belongsToKey !== $relation->getForeignKeyName()) {
                        $entity = 'relations.'.Str::beforeLast($field['name'], '.').'.values.'.$relation->getForeignKeyName();
                        Arr::set($relationDetails, $entity, Arr::get($input, $relationEntity));
                        return $relationDetails;
                    }
                }
                if(!isset($fieldDetails['values'])) {
                    $fieldDetails['values'] = Arr::get($input, $relationEntity);
                }
            }else{
                $key = implode('.relations.', explode('.', $this->getOnlyRelationEntity(['entity' => $parent['entity'].'.'.$field['entity']])));
                
                $fieldDetails = Arr::get($relationDetails, 'relations.'.$key, []);
                $related_field = $this->getCleanStateFields()[$parent['name']];
                $parent_value = is_array(Arr::get($input, $parent['name'])) ? current(Arr::get($input, $parent['name'])) : Arr::get($input, $parent['name']);
               
                switch($field['relation_type']) {
                    case 'HasOne':
                    case 'MorphOne':
                        if(isset($field['subfields'])) {
                            $fieldDetails['values'] = array_merge($fieldDetails['values'] ?? [], Arr::get($parent_value, $relationEntity) ?? []);
                        }else{
                            $fieldDetails['values'][$attributeName] = Arr::get($parent_value, $relationEntity);
                        }
                    break;
                    
                    case 'BelongsTo':
                        $fieldDetails['values'] = Arr::get($parent_value, $relationEntity);
                    break;
                    case 'HasMany':
                    case 'MorphMany':
                    case 'BelongsToMany':
                    case 'MorphToMany':
                        $fieldDetails['values'] = Arr::get($parent_value, $relationEntity);
                    break;
                }

                switch($related_field['relation_type']) {
                    case 'HasOne':
                    case 'MorphOne':
                        
                        if($field['relation_type'] === 'BelongsTo') {
                                $relation = $this->getRelationInstance(['entity' => $related_field['entity'].'.'.$field['entity']]);
                                $belongsToKey = $field['name'];
                                if(Str::contains($field['name'], '.')) {
                                    $belongsToKey = Str::afterLast($field['name'], '.');
                                }
                                if($belongsToKey !== $relation->getForeignKeyName()) {
                                    $key = 'relations.'.Str::beforeLast($key, '.relations').'.values.'. $relation->getForeignKeyName();
                                    Arr::set($relationDetails, $key, Arr::get($parent_value, $relationEntity));
                                    unset($relationDetails['relations'][$parent['name']]['values'][$field['name']]);
                                    return $relationDetails;
                                }
                            }
                            break;
                        }

                $relationEntity = $this->getOnlyRelationEntity(['entity' => $parent['entity'].'.'.$field['entity']]);
                        
            }
            
            
            $fieldDetails['model'] = $field['model'];
            $fieldDetails['parent'] = $this->getRelationModel($relationEntity, -1);
            $fieldDetails['entity'] = $relationEntity;
            $fieldDetails['attribute'] = $field['attribute'];
            $fieldDetails['relation_type'] = $field['relation_type'];

            if (isset($field['fallback_id'])) {
                $fieldDetails['fallback_id'] = $field['fallback_id'];
            }
            if (isset($field['force_delete'])) {
                $fieldDetails['force_delete'] = $field['force_delete'];
            }
        
            Arr::set($relationDetails, 'relations.'.$key, $fieldDetails);
            return $relationDetails;

    }

    /**
     * Returns an array of field names, after we keep only what's before the dots.
     * Field names that use dot notation are considered as being "grouped fields"
     * eg: address.city, address.postal_code
     * And for all those fields, this function will only return one field name (what is before the dot).
     *
     * @param  array  $fields  - the fields from where the name would be returned.
     * @return array
     */
    private function getFieldNamesBeforeFirstDot($fields)
    {
        $field_names_array = [];

        foreach ($fields as $field) {
            $field_names_array[] = Str::before($field['name'], '.');
        }

        return array_unique($field_names_array);
    }
}
