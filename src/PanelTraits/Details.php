<?php

namespace Backpack\CRUD\PanelTraits;

trait Details
{

    // ------------
    // DETAILS
    // ------------

    /**
     * Add a detail to display.
     */
    public function addDetail($detail, $type = 'details')
    {
        // if the complete_detail_array array is a string, it means the programmer was lazy and has only passed the name
        // set some default values, so the detail will still work
        if (is_string($detail)) {
            $complete_detail_array['name'] = $detail;
        } else {
            $complete_detail_array = $detail;
        }

        // if the label is missing, we should set it
        if (! isset($complete_detail_array['label'])) {
            $complete_detail_array['label'] = ucfirst($complete_detail_array['name']);
        }

        // store the field information into the correct variable on the CRUD object
        switch (strtolower($type)) {
            case 'statistic':
                $this->statistics_array[$complete_detail_array['name']] = $complete_detail_array;
                break;

            default:
                $this->details_array[$complete_detail_array['name']] = $complete_detail_array;
                break;
        }

        return $this;
    }

    public function addDetails($details)
    {
        if (count($details)) {
            foreach ($details as $detail) {
                $this->addDetail($detail);
            }
        }
    }

    /**
     * Moves the recently added detail to 'after' the $target_detail.
     *
     * @param $target_detail
     */
    public function afterDetail($target_detail)
    {
        foreach ($this->details_array as $detail => $value) {
            if ($value['name'] == $target_detail) {
                array_splice($this->details_array, $detail + 1, 0, [$detail => array_pop($this->details_array)]);
                break;
            }
        }
    }

    /**
     * Moves the recently added detail to 'before' the $target_detail.
     *
     * @param $target_detail
     */
    public function beforeDetail($target_detail)
    {
        $key = 0;
        foreach ($this->details_array as $detail => $value) {
            if ($value['name'] == $target_detail) {
                array_splice($this->details_array, $key, 0, [$detail => array_pop($this->details_array)]);
                break;
            }
            $key++;
        }
    }

    /**
     * Remove a certain detail from the create/update/both forms by its name.
     *
     * @param string $name detail name (as defined with the addDetail() procedure)
     * @param string $form update/create/both
     */
    public function removeDetail($name)
    {
        array_forget($this->details_array, $name);
    }

    /**
     * Remove many details from the create/update/both forms by their name.
     *
     * @param array  $array_of_names A simple array of the names of the details to be removed.
     */
    public function removeDetails($array_of_names)
    {
        if (! empty($array_of_names)) {
            foreach ($array_of_names as $name) {
                $this->removeDetail($name);
            }
        }
    }

    /**
     * Get the relationships used in the CRUD columns.
     * @return [array] Relationship names
     */
    public function getDetailRelationships()
    {
        $columns = $this->getColumns();

        return collect($columns)->pluck('entity')->reject(function ($value, $key) {
            return $value == null;
        })->toArray();
    }

    public function getDetails($section = 'details')
    {
        if($section == 'statistics') {
            return $this->statistics_array;
        }

        return $this->details_array;
    }
}
