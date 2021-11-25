<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
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
     * @param  array  $data  All input values to be inserted.
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($data)
    {
        $data = $this->decodeJsonCastedAttributes($data);
        $data = $this->compactFakeFields($data);

        // omit all relationships except BelongsTo when creating the entry
        $relationship_field_names = $this->getRelationshipFieldNamesToExclude();
        
        $data = $this->changeBelongsToNamesFromRelationshipToForeignKey($data);

        $item = $this->model->create(Arr::except($data, $relationship_field_names));
        
        $relation_data = $this->getRelationDataFromFormData($data);
        
        // handle the creation of the model relations after the main entity is created.
        $this->createRelationsForItem($item, $relation_data);

        return $item;
    }

    protected function getRelationshipFieldNamesToExclude() {
        $fields = $this->parseRelationFieldNamesFromHtml($this->getRelationFields());
        // we want the main entry BelongsTo relations to go through
        $fields = array_filter($fields, function($field) {
            return $field['relation_type'] !== 'BelongsTo' || ($field['relation_type'] === 'BelongsTo' && Str::contains($field['name'], '.'));
        });

        // we check if any of the field names to be removed contains a dot, if so, we remove all fields from array with same key.
        // example: HasOne Address -> address.street, address.country, would remove whole `address` instead of both single fields
        return array_unique(array_map(function($field_name) {
            if(Str::contains($field_name, '.')) {
                return Str::before($field_name, '.');
            }
            return $field_name;
        },Arr::pluck($fields, 'name')));
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
     * Get all fields with relation set
     *
     * @return array The fields with model key set.
     */
    public function getRelationFields()
    {
        $fields = $this->fields();

        return array_filter($fields, function($field) {
            return isset($field['relation_type']);
        });
    }

    /**
     * Get all fields with n-n relation set (pivot table is true).
     *
     * @return array The fields with n-n relationships.
     */
    public function getRelationFieldsWithPivot()
    {
        $all_relation_fields = $this->getRelationFields();

        return Arr::where($all_relation_fields, function ($value, $key) {
            return isset($value['pivot']) && $value['pivot'];
        });
    }

    /**
     * Create the relations for the current model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $item  The current CRUD model.
     * @param  array  $data  The form data.
     */
    public function createRelations($item, $data)
    {
        $relationData = $this->getRelationDataFromFormData($data);

        // handles 1-1 and 1-n relations (HasOne, MorphOne, HasMany, MorphMany)
        $this->createRelationsForItem($item, $relationData);

        // this specifically handles M-M relations that could sync additional information into pivot table
        $this->syncPivot($item, $data);
    }

    /**
     * Sync the declared many-to-many associations through the pivot field.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model  The current CRUD model.
     * @param  array  $data  The form data.
     */
    public function syncPivot($model, $data)
    {
        $fields_with_relationships = $this->getRelationFields();
        foreach ($fields_with_relationships as $key => $field) {
            if (isset($field['pivot']) && $field['pivot']) {
                $values = isset($data[$field['name']]) ? $data[$field['name']] : [];

                // if a JSON was passed instead of an array, turn it into an array
                if (is_string($values)) {
                    $values = json_decode($values);
                }

                $relation_data = [];
                foreach ($values as $pivot_id) {
                    $pivot_data = [];

                    if (isset($field['pivotFields'])) {
                        foreach ($field['pivotFields'] as $pivot_field_name) {
                            $pivot_data[$pivot_field_name] = $data[$pivot_field_name][$pivot_id];
                        }
                    }
                    $relation_data[$pivot_id] = $pivot_data;
                }

                $model->{$field['name']}()->sync($relation_data);
            }

            if (isset($field['morph']) && $field['morph'] && isset($data[$field['name']])) {
                $values = $data[$field['name']];
                $model->{$field['name']}()->sync($values);
            }
        }
    }

    /**
     * Create any existing one to one relations for the current model from the form data.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $item  The current CRUD model.
     * @param  array  $data  The form data.
     */
    private function createOneToOneRelations($item, $data)
    {
        $relationData = $this->getRelationDataFromFormData($data);
        $this->createRelationsForItem($item, $relationData);
    }

    /**
     * Create any existing relations for the current model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $item  The current CRUD model.
     * @param  array  $formattedData  The form data.
     * @return bool|null
     */
    private function createRelationsForItem($item, $formattedData)
    {
        if (! isset($formattedData['relations'])) {
            return false;
        }
        foreach ($formattedData['relations'] as $relationMethod => $relationData) {
            if (! isset($relationData['model'])) {
                continue;
            }
            $relation = $item->{$relationMethod}();
            $relation_type = get_class($relation);

            switch ($relation_type) {
                case HasOne::class:
                case MorphOne::class:
                        $modelInstance = $relation->updateOrCreate([], $relationData['values']);
                    break;

                case HasMany::class:
                case MorphMany::class:
                    $relation_values = $relationData['values'][$relationMethod];

                    if (is_string($relation_values)) {
                        $relation_values = json_decode($relationData['values'][$relationMethod], true);
                    }

                    if ($relation_values === null || count($relation_values) == count($relation_values, COUNT_RECURSIVE)) {
                        $this->attachManyRelation($item, $relation, $relationMethod, $relationData, $relation_values);
                    } else {
                        $this->createManyEntries($item, $relation, $relationMethod, $relationData);
                    }
                    break;
            }

            if (isset($relationData['relations'])) {
                $this->createRelationsForItem($modelInstance, ['relations' => $relationData['relations']]);
            }
        }
    }

    /**
     * Get a relation data array from the form data.
     * For each relation defined in the fields through the entity attribute, set the model, the parent model and the
     * attribute values.
     *
     * We traverse this relation array later to create the relations, for example:
     *
     * Current model HasOne Address, this Address (line_1, country_id) BelongsTo Country through country_id in Address Model.
     *
     * So when editing current model crud user have two fields address.line_1 and address.country (we infer country_id from relation)
     *
     * Those will be nested accordingly in this relation array, so address relation will have a nested relation with country.
     *
     *
     * @param  array  $data  The form data.
     * @return array The formatted relation data.
     */
    private function getRelationDataFromFormData($data)
    {
        $fields = $this->parseRelationFieldNamesFromHtml($this->getRelationFields());
        
        // exclude the already attached belongs to relations but include nested belongs to.
        $relation_fields = Arr::where($fields, function ($field, $key) {
            return $field['relation_type'] !== 'BelongsTo' || ($field['relation_type'] === 'BelongsTo' && Str::contains($field['name'], '.'));
        });
        
        $relation_data = [];

        foreach ($relation_fields as $relation_field) {
            $attributeKey = $relation_field['name'];
            $relation_entity = $this->getOnlyRelationEntity($relation_field);
            $key = implode('.relations.', explode('.', $relation_entity));
            $field_data = Arr::get($relation_data, 'relations.'.$key, []);

            if (! array_key_exists('model', $field_data)) {
                $field_data['model'] = $relation_field['model'];
            }
            if (! array_key_exists('parent', $field_data)) {
                $field_data['parent'] = $this->getRelationModel($relation_entity, -1);
            }

            // when using HasMany/MorphMany if fallback_id is provided instead of deleting the models
            // from database we resort to this fallback provided by developer
            if (array_key_exists('fallback_id', $relation_field)) {
                $field_data['fallback_id'] = $relation_field['fallback_id'];
            }

            // when using HasMany/MorphMany and column is nullable, by default backpack sets the value to null.
            // this allow developers to override that behavior and force deletion from database
            $field_data['force_delete'] = $relation_field['force_delete'] ?? false;

            if (! array_key_exists('relation_type', $field_data)) {
                $field_data['relation_type'] = $relation_field['relation_type'];
            }

            $related_attribute = Arr::last(explode('.', $attributeKey));

            $attribute_to_get_from_data_array = $attributeKey;

            if($field_data['relation_type'] === 'BelongsTo') {
                $model_instance = new $field_data['parent'];
                $relation = $model_instance->{$related_attribute}();
                $attribute_to_get_from_data_array = Arr::has($data, $attributeKey) ? $attributeKey : Str::beforeLast($attributeKey, '.').'.'.$relation->getForeignKeyName();
            } 

            $field_data['values'][$related_attribute] = Arr::get($data, $attribute_to_get_from_data_array);

            Arr::set($relation_data, 'relations.'.$key, $field_data);
        }
   
        $relation_data = $this->mergeBelongsToRelationsIntoRelationData($relation_data);
        
        return $relation_data;
    }

    private function mergeBelongsToRelationsIntoRelationData($relation_data) {
        foreach($relation_data['relations'] as  $key => $data) {
            if(isset($data['relations'])) {
                foreach($data['relations'] as $nested_key => $nested_relation) {
                    if($nested_relation['relation_type'] === 'BelongsTo') {
                        $model_instance = new $nested_relation['parent'];
                        $relation = $model_instance->{$nested_key}();
                        $relation_data['relations'][$key]['values'][$relation->getForeignKeyName()] = array_key_exists($relation->getRelationName(), $nested_relation['values']) ? $nested_relation['values'][$relation->getRelationName()] : $nested_relation['values'][$relation->getForeignKeyName()];
                        unset($relation_data['relations'][$key]['relations'][$nested_key]);
                    }
                }
            }
        }
        return $relation_data;
    }
}
