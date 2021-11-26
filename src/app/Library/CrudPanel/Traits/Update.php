<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Update
{
    /*
    |--------------------------------------------------------------------------
    |                                   UPDATE
    |--------------------------------------------------------------------------
    */

    /**
     * Update a row in the database.
     *
     * @param  int  $id  The entity's id
     * @param  array  $data  All inputs to be updated.
     * @return object
     */
    public function update($id, $data)
    {
        $data = $this->decodeJsonCastedAttributes($data);
        $data = $this->compactFakeFields($data);
        $item = $this->model->findOrFail($id);

        // omit all relationships except BelongsTo when creating the entry
        $relationship_field_names = $this->getRelationshipFieldNamesToExclude();

        $data = $this->changeBelongsToNamesFromRelationshipToForeignKey($data);

        $relation_data = $this->getRelationDataFromFormData($data);

        // handle the creation of the model relations.
        $this->createRelationsForItem($item, $relation_data);

        $updated = $item->update(Arr::except($data, $relationship_field_names));

        return $item;
    }

    /**
     * Get all fields needed for the EDIT ENTRY form.
     *
     * @param  int  $id  The id of the entry that is being edited.
     * @return array The fields with attributes, fake attributes and values.
     */
    public function getUpdateFields($id = false)
    {
        $fields = $this->fields();
        $entry = ($id != false) ? $this->getEntry($id) : $this->getCurrentEntry();

        foreach ($fields as &$field) {
            // set the value
            if (! isset($field['value'])) {
                if (isset($field['subfields'])) {
                    $field['value'] = [];
                    foreach ($field['subfields'] as $subfield) {
                        $field['value'][] = $entry->{$subfield['name']};
                    }
                } else {
                    $field['value'] = $this->getModelAttributeValue($entry, $field);
                }
            }
        }

        // always have a hidden input for the entry id
        if (! array_key_exists('id', $fields)) {
            $fields['id'] = [
                'name'  => $entry->getKeyName(),
                'value' => $entry->getKey(),
                'type'  => 'hidden',
            ];
        }

        return $fields;
    }

    /**
     * Get the value of the 'name' attribute from the declared relation model in the given field.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model  The current CRUD model.
     * @param  array  $field  The CRUD field array.
     * @return mixed The value of the 'name' attribute from the relation model.
     */
    private function getModelAttributeValue($model, $field)
    {
        if (isset($field['entity']) && $field['entity'] !== false) {
            $relational_entity = $this->parseRelationFieldNameFromHtml($field['name']);

            $relation_array = explode('.', $relational_entity);

            $related_model = array_reduce(array_splice($relation_array, 0, -1), function ($obj, $method) {
                return $obj->{$method} ? $obj->{$method} : $obj;
            }, $model);

            $relation_method = Str::afterLast($relational_entity, '.');

            if (method_exists($related_model, $relation_method)) {
                $relation_type = get_class($related_model->{$relation_method}());
                switch ($relation_type) {
                    case MorphMany::class:
                    case HasMany::class:
                    case BelongsToMany::class:
                    case MorphToMany::class:
                        if (isset($field['pivotFields']) && is_array($field['pivotFields'])) {
                            $pivot_fields = Arr::where($field['pivotFields'], function ($item) use ($field) {
                                return $field['name'] != $item['name'];
                            });
                            $related_models = $model->{$relation_method};
                            $result = [];

                            // for any given model, we grab the attributes that belong to our pivot table.
                            foreach ($related_models as $related_model) {
                                $item = [];
                                switch ($relation_type) {
                                    case HasMany::class:
                                    case MorphMany::class:
                                        // for any given related model, we get the value from pivot fields
                                        foreach ($pivot_fields as $pivot_field) {
                                            $item[$pivot_field['name']] = $related_model->{$pivot_field['name']};
                                        }
                                        $item[$related_model->getKeyName()] = $related_model->getKey();
                                        $result[] = $item;
                                        break;

                                    case BelongsToMany::class:
                                    case MorphToMany::class:
                                        // for any given related model, we get the pivot fields.
                                        foreach ($pivot_fields as $pivot_field) {
                                            $item[$pivot_field['name']] = $related_model->pivot->{$pivot_field['name']};
                                        }
                                        $item[$field['name']] = $related_model->getKey();
                                        $result[] = $item;
                                        break;
                                }
                            }

                            return $result;
                        }

                        break;
                    case HasOne::class:
                    case MorphOne::class:
                        return $related_model->{$relation_method}->{Str::afterLast($relational_entity, '.')};
                        break;
                }
            }

            return $related_model->{$relation_method};
        }

        if (is_string($field['name'])) {
            return $model->{$field['name']};
        }

        if (is_array($field['name'])) {
            $result = [];
            foreach ($field['name'] as $key => $value) {
                $result = $model->{$value};
            }

            return $result;
        }
    }
}
