<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

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
     * @param int   $id   The entity's id
     * @param array $data All inputs to be updated.
     *
     * @return object
     */
    public function update($id, $data)
    {
        $data = $this->decodeJsonCastedAttributes($data);
        $data = $this->compactFakeFields($data);
        $item = $this->model->findOrFail($id);

        $this->createRelations($item, $data);

        // omit the n-n relationships when updating the eloquent item
        $nn_relationships = array_pluck($this->getRelationFieldsWithPivot(), 'name');
        $data = array_except($data, $nn_relationships);
        $updated = $item->update($data);

        return $item;
    }

    /**
     * Get all fields needed for the EDIT ENTRY form.
     *
     * @param int $id The id of the entry that is being edited.
     *
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

                    // handle fake fields
                } elseif (! empty($field['fake'])) {
                    // determine the stored-in attribute
                    $fakeStoredInAttribute = $field['store_in'] ?? 'extras';
                    // check if the fake stored-in attribute exists
                    if (! empty($entry->{$fakeStoredInAttribute})) {
                        $fakeStoredInArray = $entry->{$fakeStoredInAttribute};
                        // check if it's a string, decode it
                        // otherwise, it should be an array
                        if (is_string($fakeStoredInArray)) {
                            // decode it
                            $fakeStoredInArray = json_decode($fakeStoredInArray, true);
                        }

+                        if (! empty($fakeStoredInArray) && is_array($fakeStoredInArray) && isset($fakeStoredInArray[$field['name']])) {
                            $field['value'] = $fakeStoredInArray[$field['name']];
                        }
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
     * @param \Illuminate\Database\Eloquent\Model $model The current CRUD model.
     * @param array                               $field The CRUD field array.
     *
     * @return mixed The value of the 'name' attribute from the relation model.
     */
    private function getModelAttributeValue($model, $field)
    {
        if (isset($field['entity'])) {
            $relationArray = explode('.', $field['entity']);
            $relatedModel = array_reduce(array_splice($relationArray, 0, -1), function ($obj, $method) {
                return $obj->{$method} ? $obj->{$method} : $obj;
            }, $model);

            $relationMethod = end($relationArray);
            if ($relatedModel->{$relationMethod} && $relatedModel->{$relationMethod}() instanceof HasOneOrMany) {
                return $relatedModel->{$relationMethod}->{$field['name']};
            } else {
                return $relatedModel->{$field['name']};
            }
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
