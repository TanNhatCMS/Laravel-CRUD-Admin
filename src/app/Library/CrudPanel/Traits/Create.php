<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;

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
     * @param array $data All input values to be inserted.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($data)
    {
        $data = $this->decodeJsonCastedAttributes($data);
        $data = $this->compactFakeFields($data);

        // omit the relationships when initing the model
        $relationships = Arr::pluck($this->getRelationFields(), 'name');

        // init and fill model
        $item = $this->model->make(Arr::except($data, $relationships));

        // handle BelongsTo 1:1 relations
        $item = $this->associateOrDissociateBelongsToRelations($item, $data);
        $item->save();

        // if there are any other relations create them.
        $this->createRelations($item, $data);

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
     * Associate and dissociate BelongsTo relations in the model.
     *
     * @param  Model $item
     * @param  array $data The form data.
     * @return Model Model with relationships set up.
     */
    protected function associateOrDissociateBelongsToRelations($item, array $data)
    {
        $belongsToFields = $this->getFieldsWithRelationType('BelongsTo');

        foreach ($belongsToFields as $relationField) {
            if (method_exists($item, $this->getOnlyRelationEntity($relationField))) {
                $relatedId = Arr::get($data, $relationField['name']);
                if (isset($relatedId) && $relatedId !== null) {
                    $related = $relationField['model']::find($relatedId);

                    $item->{$this->getOnlyRelationEntity($relationField)}()->associate($related);
                } else {
                    $item->{$this->getOnlyRelationEntity($relationField)}()->dissociate();
                }
            }
        }

        return $item;
    }

    /**
     * Get all fields with relation set (model key set on field).
     *
     * @return array The fields with model key set.
     */
    public function getRelationFields()
    {
        $fields = $this->fields();
        $relationFields = [];

        foreach ($fields as $field) {
            if (isset($field['model']) && $field['model'] !== false) {
                array_push($relationFields, $field);
            }

            if (isset($field['subfields']) &&
                is_array($field['subfields']) &&
                count($field['subfields'])) {
                foreach ($field['subfields'] as $subfield) {
                    array_push($relationFields, $subfield);
                }
            }
        }

        return $relationFields;
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
     * @param \Illuminate\Database\Eloquent\Model $item The current CRUD model.
     * @param array                               $data The form data.
     */
    public function createRelations($item, $data)
    {
        $this->syncPivot($item, $data);
        $this->createOneToOneRelations($item, $data);
    }

    /**
     * Sync the declared many-to-many associations through the pivot field.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The current CRUD model.
     * @param array                               $data  The form data.
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
     * @param \Illuminate\Database\Eloquent\Model $item The current CRUD model.
     * @param array                               $data The form data.
     */
    private function createOneToOneRelations($item, $data)
    {
        $relationData = $this->getRelationDataFromFormData($data);
        $this->createRelationsForItem($item, $relationData);
    }

    /**
     * Create any existing one to one relations for the current model from the relation data.
     *
     * @param \Illuminate\Database\Eloquent\Model $item          The current CRUD model.
     * @param array                               $formattedData The form data.
     *
     * @return bool|null
     */
    private function createRelationsForItem($item, $formattedData)
    {
        if (! isset($formattedData['relations'])) {
            return false;
        }
        foreach ($formattedData['relations'] as $relationMethod => $relationData) {
            $relation = $item->{$relationMethod}();

            if ($relation instanceof HasOne) {
                if (isset($relationData['relations'])) {
                    // if there are nested relations, we first add the BelongsTo like in main entry
                    $belongsToRelations = Arr::where($relationData['relations'], function ($relation_data) {
                        return $relation_data['relation_type'] == 'BelongsTo';
                    });

                    // adds the values of the BelongsTo relations of this entity to the array of values that will
                    // be saved at the same time like we do in parent entity belongs to relations
                    $valuesWithRelations = $this->associateHasOneBelongsTo($belongsToRelations, $relationData['values'] ?? [], $relation->getModel());

                    // remove previously added BelongsTo relations from relation data.
                    $relationData['relations'] = Arr::where($relationData['relations'], function ($item) {
                        return $item['relation_type'] != 'BelongsTo';
                    });

                    $modelInstance = $relation->updateOrCreate([], $valuesWithRelations);
                } else {
                    $modelInstance = $relation->updateOrCreate([], $relationData['values']);
                }
            }

            if (isset($relationData['relations'])) {
                $this->createRelationsForItem($modelInstance, ['relations' => $relationData['relations']]);
            }
        }
    }

    /**
     * Associate the nested HasOne -> BelongsTo relations by adding the "connecting key"
     * to the array of values that is going to be saved with HasOne relation.
     *
     * @param array $belongsToRelations
     * @param array $modelValues
     * @param Model $relationInstance
     * @return array
     */
    private function associateHasOneBelongsTo($belongsToRelations, $modelValues, $modelInstance)
    {
        foreach ($belongsToRelations as $methodName => $values) {
            $relation = $modelInstance->{$methodName}();

            $modelValues[$relation->getForeignKeyName()] = $values['values'][$methodName];
        }

        return $modelValues;
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
     * @param array $data The form data.
     *
     * @return array The formatted relation data.
     */
    private function getRelationDataFromFormData($data)
    {
        $relation_fields = Arr::where($this->getRelationFields(), function ($field, $key) {
            return $field['relation_type'] !== 'BelongsTo' || $this->isNestedRelation($field);
        });

        $relationData = [];
        foreach ($relation_fields as $relation_field) {
            $attributeKey = $this->parseRelationFieldNamesFromHtml([$relation_field])[0]['name'];
            if (isset($relation_field['pivot']) && $relation_field['pivot'] !== true) {
                $key = implode('.relations.', explode('.', $this->getOnlyRelationEntity($relation_field)));
                $fieldData = Arr::get($relationData, 'relations.'.$key, []);
                if (! array_key_exists('model', $fieldData)) {
                    $fieldData['model'] = $relation_field['model'];
                }
                if (! array_key_exists('parent', $fieldData)) {
                    $fieldData['parent'] = $this->getRelationModel($attributeKey, -1);
                }

                if (! array_key_exists('relation_type', $fieldData)) {
                    $fieldData['relation_type'] = $relation_field['relation_type'];
                }

                $relatedAttribute = Arr::last(explode('.', $attributeKey));
                $fieldData['values'][$relatedAttribute] = Arr::get($data, $attributeKey);

                Arr::set($relationData, 'relations.'.$key, $fieldData);
            }
        }

        return $relationData;
    }
}
