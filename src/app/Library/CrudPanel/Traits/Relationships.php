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
        $possible_method = Str::before($entity, '.');
        $model = $this->model;

        if (method_exists($model, $possible_method)) {
            $parts = explode('.', $entity);
            // here we are going to iterate through all relation parts to check
            foreach ($parts as $i => $part) {
                $relation = $model->$part();
                $model = $relation->getRelated();
            }

            return $relation;
        }
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
     * Get the fields for relationships, according to the relation type. It looks only for direct
     * relations - it will NOT look through relationships of relationships.
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
            ->filter(function ($item) {
                $related_model = get_class($this->model->{Str::before($item['entity'], '.')}()->getRelated());

                return Str::contains($item['entity'], '.') && $item['model'] !== $related_model ? false : true;
            })
            ->toArray();
    }

    protected function changeBelongsToNamesFromRelationshipToForeignKey($data)
    {
        $belongs_to_fields = $this->getFieldsWithRelationType('BelongsTo');

        foreach ($belongs_to_fields as $relation_field) {
            $relation = $this->getRelationInstance($relation_field);
            if (Arr::has($data, $relation->getRelationName())) {
                $data[$relation->getForeignKeyName()] = Arr::get($data, $relation->getRelationName());
                unset($data[$relation->getRelationName()]);
            }
        }

        return $data;
    }


    /**
     * Get the CRUD fields for the current operation, but with
     * their names ready to be used inside inputs. For example
     * 'relationship.attribute' => 'relationship[attribute]'
     *
     * @return array
     */
    public function fieldsWithOverwrittenNamesForHtml($fields = null)
    {
        $fields = $fields ?? $this->fields();

        foreach ($fields as $key => $field) {
            $fields[$key] = $this->overwriteFieldNameFromDotNotationToArray($field);
        }

        return $fields;
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
            case 'HasManyThrough':
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
            case 'HasManyThrough':
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
            case 'HasManyThrough':
            case 'MorphMany':
            case 'MorphOneOrMany':
            case 'MorphToMany':
                return true;
            default:
                return false;
        }
    }
}
