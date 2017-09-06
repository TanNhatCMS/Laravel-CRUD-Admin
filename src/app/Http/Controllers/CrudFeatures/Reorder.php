<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

trait Reorder
{
    /**
     * Filters entries collection before rendering the reorder view.
     */
    protected $reorder_filter_callback = null;

    /**
     *  Reorder the items in the database using the Nested Set pattern.
     *
     *  Database columns needed: id, parent_id, lft, rgt, depth, name/title
     *
     *  @return Response
     */
    public function reorder()
    {
        $this->crud->hasAccessOrFail('reorder');

        if (! $this->crud->isReorderEnabled()) {
            abort(403, 'Reorder is disabled.');
        }

        // get all results for that entity
        $this->data['entries'] = $this->crud->getEntries();
        $this->data['entries'] = $this->data['entries']->filter($this->reorder_filter_callback);
        $this->data['crud'] = $this->crud;
        $this->data['title'] = trans('backpack::crud.reorder').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getReorderView(), $this->data);
    }

    /**
     * Save the new order, using the Nested Set pattern.
     *
     * Database columns needed: id, parent_id, lft, rgt, depth, name/title
     *
     * @return
     */
    public function saveReorder()
    {
        $this->crud->hasAccessOrFail('reorder');

        $all_entries = \Request::input('tree');

        if (count($all_entries)) {
            $count = $this->crud->updateTreeOrder($all_entries);
        } else {
            return false;
        }

        return 'success for '.$count.' items';
    }

    /**
     * Set a callable for filtering the items to reorder.
     *
     * @param callable $callable
     * @internal param callable $reorder_filter_callback
     */
    public function setReorderFilterCallback(callable $callable)
    {
        $this->reorder_filter_callback = $callable;
    }
}
