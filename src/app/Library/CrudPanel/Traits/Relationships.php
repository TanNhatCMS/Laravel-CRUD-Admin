<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Relationships
{
    /**
     * From the field entity we get the relation instance.
     *
     * @param  array  $entity
     * @return object
     */
    public function getRelationInstance($field)
    {
        $entity = $this->getOnlyRelationEntity($field);

        $model = $this->model;

        $parts = explode('.', $entity);
        // here we are going to iterate through all relation parts to check
        foreach ($parts as $i => $part) {
            try {
                $relation = $model->$part();
                $model = $relation->getRelated();
            } catch (\Exception $e) {
                return $relation;
            }
        }

        return $relation;
    }

    /**
     * Grabs an relation instance and returns the class name of the related model.
     *
     * @param  array  $field
     * @return string
     */
    public function inferFieldModelFromRelationship($field)
    {
        $relation = $this->getRelationInstance($field);

        return get_class($relation->getRelated());
    }

    /**
     * Return the relation type from a given field: BelongsTo, HasOne ... etc.
     *
     * @param  array  $field
     * @return string
     */
    public function inferRelationTypeFromRelationship($field)
    {
        $relation = $this->getRelationInstance($field);

        return Arr::last(explode('\\', get_class($relation)));
    }

    /**
     * Return only the related entity excluding the attribute if present in the relation string.
     *  
     * @param array $relation_field - the crud field
     * @return string the relation entity
     */
    public function getOnlyRelationEntity($relation_field)
    {
        $relation_model = $this->getRelationModel($relation_field['entity'], -1);
        $related_method = Str::afterLast($relation_field['entity'], '.');

        if (! method_exists($relation_model, $related_method)) {
            return Str::beforeLast($relation_field['entity'], '.');
        }

        return $relation_field['entity'];
    }

    /**
     * Get the fields for with given relation type.
     *
     * @param  string|array  $relation_types  Eloquent relation class or array of Eloquent relation classes. Eg: BelongsTo
     * @return array The fields with corresponding relation types.
     */
    public function getFieldsWithRelationType($relation_types): array
    {
        $relation_types = (array) $relation_types;

        return collect($this->fields())
            ->where('model')
            ->whereIn('relation_type', $relation_types)
            ->toArray();
    }

    /**
     * Parse the field name back to the related entity after the form is submited.
     * Its called in getAllFieldNames().
     *
     * @param  array  $fields
     * @return array
     */
    private function parseRelationFieldNamesFromHtml($fields)
    {
        foreach ($fields as &$field) {
            //we only want to parse fields that has a relation type
            if (isset($field['relation_type'])) {
                $field['name'] = $this->parseRelationFieldNameFromHtml($field['name']);
            }
        }

        return $fields;
    }

    /**
     * Parse the field name back to the related entity after the form is submited.
     * Its called in getAllFieldNames().
     *
     * @param  string  $field_name
     * @return array
     */
    private function parseRelationFieldNameFromHtml($field_name)
    {
        //we only want to parse fields that name contains [ ] used in html.
        if (preg_match('/[\[\]]/', $field_name) !== 0) {
            $chunks = explode('[', $field_name);

            foreach ($chunks as &$chunk) {
                if (strpos($chunk, ']')) {
                    $chunk = str_replace(']', '', $chunk);
                }
            }

            return implode('.', $chunks);
        }

        return $field_name;
    }

    /**
     * Changes BelongsTo relation names (article) into the connected key (article_id)
     * 
     * @param array $data - the form data array
     * @return array
     */
    protected function changeBelongsToNamesFromRelationshipToForeignKey($data)
    {
        $belongs_to_fields = $this->getFieldsWithRelationType('BelongsTo');

        foreach ($belongs_to_fields as $relation_field) {
            $relation = $this->getRelationInstance($relation_field);
            $entity = $this->getOnlyRelationEntity($relation_field);
            $relation_key_to_substitute = $relation->getForeignKeyName();
            // if we are in a nested relation
            if (Str::contains($entity, '.')) {
                $relation_key_to_substitute = Str::beforeLast($entity, '.').'.'.$relation->getForeignKeyName();
            }
            if (Arr::has($data, $entity)) {
                Arr::set($data, $relation_key_to_substitute, Arr::get($data, $entity));
                Arr::forget($data, $entity);
            }
        }

        return $data;
    }

    /**
     * Based on relation type returns the default field type.
     *
     * @param  string  $relation_type
     * @return bool
     */
    public function inferFieldTypeFromFieldRelation($field)
    {
        switch ($field['relation_type']) {
            case 'BelongsToMany':
            case 'HasMany':
            case 'MorphMany':
            case 'MorphToMany':
            case 'BelongsTo':
                return 'relationship';

            default:
                return 'text';
        }
    }

    /**
     * Based on relation type returns if relation allows multiple entities.
     *
     * @param  string  $relation_type
     * @return bool
     */
    public function guessIfFieldHasMultipleFromRelationType($relation_type)
    {
        switch ($relation_type) {
            case 'BelongsToMany':
            case 'HasMany':
            case 'HasOneOrMany':
            case 'MorphMany':
            case 'MorphOneOrMany':
            case 'MorphToMany':
                return true;

            default:
                return false;
        }
    }

    /**
     * Based on relation type returns if relation has a pivot table.
     *
     * @param  string  $relation_type
     * @return bool
     */
    public function guessIfFieldHasPivotFromRelationType($relation_type)
    {
        switch ($relation_type) {
            case 'BelongsToMany':
            case 'MorphMany':
            case 'HasMany':
            case 'MorphToMany':
                return true;
            default:
                return false;
        }
    }

    /**
     * This function is reponsible to return the relationship field names that should be excluded from main entry saving process
     */
    protected function getRelationshipFieldNamesToExclude()
    {
        $fields = $this->parseRelationFieldNamesFromHtml($this->getRelationFields());
        // we want the main entry BelongsTo relations to go through
        $fields = array_filter($fields, function ($field) {
            return $field['relation_type'] !== 'BelongsTo' || ($field['relation_type'] === 'BelongsTo' && Str::contains($field['name'], '.'));
        });

        // we check if any of the field names to be removed contains a dot, if so, we remove all fields from array with same key.
        // example: HasOne Address -> address.street, address.country, would remove whole `address` instead of both single fields
        return array_unique(array_map(function ($field_name) {
            if (Str::contains($field_name, '.')) {
                return Str::before($field_name, '.');
            }

            return $field_name;
        }, Arr::pluck($fields, 'name')));
    }

    /**
     * Get all fields with n-n relation set (pivot table is true).
     *
     * @return array The fields with n-n relationships.
     */
    protected function getRelationFieldsWithPivot()
    {
        $all_relation_fields = $this->getRelationFields();

        return Arr::where($all_relation_fields, function ($value, $key) {
            return isset($value['pivot']) && $value['pivot'];
        });
    }

    /**
     * Get all fields with relation set.
     *
     * @return array The fields with relation_type key set.
     */
    public function getRelationFields()
    {
        $fields = $this->fields();

        return array_filter($fields, function ($field) {
            return isset($field['relation_type']);
        });
    }
}
