<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

trait CheckUnique
{
    /**
     * Checks if the given string is unique, and return existing entity.
     * @return JSON containing success, message and data.
     */
    public function checkUniqueString()
    {
        $response = ['success' => false, 'message' => trans('backpack::crud.unique_error'), 'meta' => ['link' => null, 'snippet' => null, 'entity_key' => null]];

        $field_name = \Request::input('field_name', null);
        $check_value = \Request::input('check_value', null);
        $display_name = \Request::input('display_name', null);

        if (empty($field_name)) {
            $response['message'] = trans('backpack::crud.unique_field_name_missing');
        } elseif (empty($check_value) && $check_value !== '0') {
            $response['message'] = trans('backpack::crud.unique_check_value_missing');
        } else {
            $existing_entity = $this->crud->model->where([$field_name => $check_value])->first();

            if (! $existing_entity) {
                $response['success'] = true;
                $response['message'] = null;
            } else {
                $response['message'] = $this->crud->entity_name.' '.trans('backpack::crud.unique_exists');
                $response['meta'] = [
                   'link' => url($this->crud->route.'/'.$existing_entity->getKey().'/edit'),
                   'snippet' => $display_name ? $existing_entity->{$display_name} : $this->crud->entity_name,
                   'entity_key' => $existing_entity->getKey(),
               ];
            }
        }

        return $response;
    }
}
