<?php

namespace Backpack\CRUD\PanelTraits;

trait Reorder
{
    /*
    |--------------------------------------------------------------------------
    |                                   REORDER
    |--------------------------------------------------------------------------
    */

    /**
     * Change the order and parents of the given elements, according to the NestedSortable AJAX call.
     *
     * If using simple reordering, the field 'seq' is required,
     * otherwise the reordering required the fields 'parent_id', 'depth', 'lft' and 'rgt'.
     *
     * @param  array  $entries  The entire request from the NestedSortable AJAX Call.
     * @return int  The number of items whose position in the tree has been changed.
     */
    public function updateTreeOrder(array $entries)
    {
        $count = 0;

        foreach ($entries as $entry) {
            if (! empty($entry['item_id'])) {
                $count++;
                $fields = $this->reorder_simple ?
                    [
                        'seq' => $count,
                    ] :
                    [
                        'parent_id' => $entry['parent_id'] ?: null,
                        'depth'     => $entry['depth'] ?: null,
                        'lft'       => $entry['lft'] ?: null,
                        'rgt'       => $entry['rgt'] ?: null,
                    ];
                optional($this->model->find($entry['item_id']))
                    ->fill($fields)
                    ->save();
            }
        }

        return $count;
    }

    /**
     * Enable the Reorder functionality in the CRUD Panel for users that have the been given access to 'reorder' using:
     * $this->crud->allowAccess('reorder');.
     *
     * If using simple reordering, the field 'seq' is required,
     * otherwise the reordering required the fields 'parent_id', 'depth', 'lft' and 'rgt'.
     *
     * @param  string  $label      Column name that will be shown on the labels.
     * @param  int     $max_level  Maximum hierarchy level for nesting of entities (1 = no nesting, just reordering).
     * @param  bool    $simple     Whether reordering should be simple. Will set max level to 1.
     * @return void
     */
    public function enableReorder($label = 'name', $max_level = 1, $simple = false)
    {
        $this->reorder = true;
        $this->reorder_label = $label;
        $this->reorder_max_level = $simple ? 1 : $max_level;
        $this->reorder_simple = $simple;
    }

    /**
     * Disable the Reorder functionality in the CRUD Panel for all users.
     *
     * @return void
     */
    public function disableReorder()
    {
        $this->reorder = false;
    }

    /**
     * Check if the Reorder functionality is enabled or not.
     *
     * @return bool
     */
    public function isReorderEnabled()
    {
        return $this->reorder;
    }
}
