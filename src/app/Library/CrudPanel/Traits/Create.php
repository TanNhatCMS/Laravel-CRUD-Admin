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
            $relation_type = $relationData['relation_type'];

            switch ($relation_type) {
                case 'HasOne':
                case 'MorphOne':
                        $modelInstance = $relation->updateOrCreate([], $relationData['values']);
                    break;

                case 'HasMany':
                case 'MorphMany':
                    $relation_values = $relationData['values'][$relationMethod];

                    // if relation values are null we can only attach, also we check if we sent a single dimensional array [1,2,3], or an array of arrays: [[1][2][3]]
                    // if is as single dimensional array we can only attach.
                    if ($relation_values === null || count($relation_values) == count($relation_values, COUNT_RECURSIVE)) {
                        $this->attachManyRelation($item, $relation, $relationMethod, $relationData, $relation_values);
                    } else {
                        $this->createManyEntries($item, $relation, $relationMethod, $relationData);
                    }
                    break;
                case 'BelongsToMany':
                case 'MorphToMany':
                    $values = $relationData['values'][$relationMethod] ?? [];

                    $values = is_string($values) ? json_decode($values, true) : $values;

                    $relation_data = [];
                    
                    foreach ($values as $value) {
                        if(!isset($value[$relationMethod])) {
                            continue;
                        }
                        
                        $relation_data[$value[$relationMethod]] = Arr::except($value, $relationMethod);
                        
                    }
                    
                    // if there is no relation data, and the values array is single dimensional we have
                    // an array of keys with no aditional pivot data. sync those.
                    if (empty($relation_data) && count($values) == count($values, COUNT_RECURSIVE)) {
                        $relation_data = array_values($values);
                    }

                    $item->{$relationMethod}()->sync($relation_data);
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

            if ($field_data['relation_type'] === 'BelongsTo') {
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

    // BelongsTo relations should be saved along with main entry. We check for `user_id` (key) or `user` (relation name).
    private function mergeBelongsToRelationsIntoRelationData($relation_data)
    {
        $data = $relation_data;
        foreach ($relation_data['relations'] ?? [] as  $key => $data) {
            if (isset($relation_data['relations'])) {
                foreach ($data['relations'] ?? [] as $nested_key => $nested_relation) {
                    if ($nested_relation['relation_type'] === 'BelongsTo') {
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

    /**
     * When using the HasMany/MorphMany relations as selectable elements we use this function to sync those relations.
     * Here we allow for different functionality than when creating. Developer could use this relation as a
     * selectable list of items that can belong to one/none entity at any given time.
     *
     * @return void
     */
    public function attachManyRelation($item, $relation, $relationMethod, $relationData, $relation_values)
    {
        $model_instance = $relation->getRelated();
        $force_delete = $relationData['force_delete'];
        $relation_foreign_key = $relation->getForeignKeyName();
        $relation_local_key = $relation->getLocalKeyName();

        $relation_column_is_nullable = $model_instance->isColumnNullable($relation_foreign_key);

        if ($relation_values !== null && $relationData['values'][$relationMethod][0] !== null) {
            // we add the new values into the relation
            $model_instance->whereIn($model_instance->getKeyName(), $relation_values)
                ->update([$relation_foreign_key => $item->{$relation_local_key}]);

            // we clear up any values that were removed from model relation.
            // if developer provided a fallback id, we use it
            // if column is nullable we set it to null if developer didn't specify `force_delete => true`
            // if none of the above we delete the model from database
            if (isset($relationData['fallback_id'])) {
                $model_instance->whereNotIn($model_instance->getKeyName(), $relation_values)
                    ->where($relation_foreign_key, $item->{$relation_local_key})
                    ->update([$relation_foreign_key => $relationData['fallback_id']]);
            } else {
                if (! $relation_column_is_nullable || $force_delete) {
                    $model_instance->whereNotIn($model_instance->getKeyName(), $relation_values)
                        ->where($relation_foreign_key, $item->{$relation_local_key})
                        ->delete();
                } else {
                    $model_instance->whereNotIn($model_instance->getKeyName(), $relation_values)
                        ->where($relation_foreign_key, $item->{$relation_local_key})
                        ->update([$relation_foreign_key => null]);
                }
            }
        } else {
            // the developer cleared the selection
            // we gonna clear all related values by setting up the value to the fallback id, to null or delete.
            if (isset($relationData['fallback_id'])) {
                $model_instance->where($relation_foreign_key, $item->{$relation_local_key})
                    ->update([$relation_foreign_key => $relationData['fallback_id']]);
            } else {
                if (! $relation_column_is_nullable || $force_delete) {
                    $model_instance->where($relation_foreign_key, $item->{$relation_local_key})->delete();
                } else {
                    $model_instance->where($relation_foreign_key, $item->{$relation_local_key})
                        ->update([$relation_foreign_key => null]);
                }
            }
        }
    }

    /**
     * Handle HasMany/MorphMany relations when used as creatable entries in the crud.
     * By using repeatable field, developer can allow the creation of such entries
     * in the crud forms.
     *
     * @return void
     */
    public function createManyEntries($entry, $relation, $relationMethod, $relationData)
    {
        $items = $relationData['values'][$relationMethod];

        $relation_local_key = $relation->getLocalKeyName();

        // if the collection is empty we clear all previous values in database if any.
        if (empty($items)) {
            $entry->{$relationMethod}()->sync([]);
        } else {
            $created_ids = [];

            foreach ($items as $item) {
                if (isset($item[$relation_local_key]) && ! empty($item[$relation_local_key])) {
                    $entry->{$relationMethod}()->updateOrCreate([$relation_local_key => $item[$relation_local_key]], $item);
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
    }
}
