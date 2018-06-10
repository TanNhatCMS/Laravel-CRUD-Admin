<?php

namespace Backpack\CRUD\PanelTraits;

trait CustomFormView
{
    //when set as wide, removes the from wrapper that makes it look narrow
    public function setFormView($custom_form_view, $is_wide = false)
    {
        $this->custom_form_view = $custom_form_view;
        $this->wide_form        = $is_wide;
    }

    public function extractFields($fields)
    {
        $f = [];
        foreach ($fields as $field) {
            $f[$field['name']] = $field;
        }
        return $f;
    }
    public function extractTemplates($fields)
    {
        $t = [];
        foreach ($fields as $field) {
            if (isset($field['view_namespace'])) {
                $t[$field['name']] = $field['view_namespace'] . '.' . $field['type'];
            } else {
                $t[$field['name']] = 'crud::fields.' . $field['type'];
            }
        }
        return $t;
    }

}
