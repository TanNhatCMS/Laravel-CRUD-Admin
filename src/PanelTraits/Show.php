<?php

namespace Backpack\CRUD\PanelTraits;

trait Show
{
    /*
    |--------------------------------------------------------------------------
    |                                   SHOW
    |--------------------------------------------------------------------------
    */

    /**
     * Add field to the show view
     *
     * @param $field
     * @return $this
     */
    public function addShowField($field)
    {
        // if the field_definition_array array is a string, it means the programmer was lazy and has only passed the name
        // set some default values, so the field will still work
        if (is_string($field)) {
            $complete_field_array['name'] = $field;
        } else {
            $complete_field_array = $field;
        }

        // if this is a relation type field and no corresponding model was specified, get it from the relation method
        // defined in the main model
        if (isset($complete_field_array['entity']) && ! isset($complete_field_array['model'])) {
            $complete_field_array['model'] = $this->getRelationModel($complete_field_array['entity']);
        }

        // if the label is missing, we should set it
        if (! isset($complete_field_array['label'])) {
            $complete_field_array['label'] = ucfirst($complete_field_array['name']);
        }

        // if the field type is missing, we should set it
        if (! isset($complete_field_array['type'])) {
            $complete_field_array['type'] = $this->getFieldTypeFromDbColumnType($complete_field_array['name']);
        }

        // if a tab was mentioned, we should enable it
        if (isset($complete_field_array['tab'])) {
            if (! $this->tabsEnabled()) {
                $this->enableTabs();
            }
        }

        // store the field information into the correct variable on the CRUD object
        // default fields are already handled in addField()
        $this->show_fields[$complete_field_array['name']] = $complete_field_array;

        return $this;
    }

    /**
     * Add multiple fields to the show view
     *
     * @param $fields
     */
    public function addShowFields($fields)
    {
        if (count($fields)) {
            foreach ($fields as $field) {
                $this->addShowField($field);
            }
        }
    }

    /**
     * Moves the recently added field to 'after' the $target_field.
     *
     * @param $target_field
     */
    public function afterShowField($target_field)
    {
        foreach ($this->show_fields as $field => $value) {
            if ($value['name'] == $target_field) {
                $offset = array_search($field, array_keys($this->show_fields));
                array_splice($this->show_fields, $offset + 1, 0, [$field => array_pop($this->create_fields)]);
                break;
            }
        }
    }

    /**
     * Moves the recently added field to 'before' the $target_field.
     *
     * @param $target_field
     */
    public function beforeShowField($target_field)
    {
        $key = 0;
        foreach ($this->show_fields as $field => $value) {
            if ($value['name'] == $target_field) {
                array_splice($this->show_fields, $key, 0, [$field => array_pop($this->show_fields)]);
                break;
            }
            $key++;
        }
    }

    /**
     * Remove a certain field from the show view by its name.
     *
     * @param string $name Field name (as defined with the addField() procedure)
     */
    public function removeShowField($name)
    {
        array_forget($this->show_fields, $name);
    }

    /**
     * Remove many fields from the show view by their names.
     *
     * @param array  $array_of_names A simple array of the names of the fields to be removed.
     */
    public function removeShowFields($array_of_names)
    {
        if (! empty($array_of_names)) {
            foreach ($array_of_names as $name) {
                $this->removeField($name);
            }
        }
    }

    /**
     * Get all fields needed for the EDIT ENTRY form.
     *
     * @param  [integer] The id of the entry that is being edited.
     * @param int $id
     *
     * @return [array] The fields with attributes, fake attributes and values.
     */
    public function getShowFields($id)
    {
        $fields = $this->show_fields;
        $entry = $this->getEntry($id);

        foreach ($fields as $k => $field) {
            // set the value
            if (! isset($fields[$k]['value'])) {
                if (isset($field['subfields'])) {
                    $fields[$k]['value'] = [];
                    foreach ($field['subfields'] as $key => $subfield) {
                        $fields[$k]['value'][] = $entry->{$subfield['name']};
                    }
                } else {
                    $fields[$k]['value'] = $entry->{$field['name']};
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
     * Allow access to the show crud functionality
     */
    public function enableShow() {
        $this->allowAccess(['show']);
    }
}
