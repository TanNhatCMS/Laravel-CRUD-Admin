<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Route;

trait TrashOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupTrashRoutes($segment, $routeName, $controller)
    {
        Route::delete($segment.'/{id}/delete-permanently', [
            'as'        => $routeName.'.delete_permanently',
            'uses'      => $controller.'@deletePermanently',
            'operation' => 'delete_permanently',
        ]);
        Route::put($segment.'/{id}/restore', [
            'as'        => $routeName.'.restore',
            'uses'      => $controller.'@restore',
            'operation' => 'restore',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupTrashDefaults()
    {
        CRUD::allowAccess('restore');
        CRUD::allowAccess('delete_permanently');

        CRUD::operation('delete_permanently', function () {
            CRUD::loadDefaultOperationSettingsFromConfig();
        });

        CRUD::operation('list', function () {
            $this->crud->addFilter([
                'type'  => 'simple',
                'name'  => 'trashed',
                'label' => 'Trashed'
              ],
              false,
              function() { // if the filter is active
                $this->crud->query->onlyTrashed();
                $this->crud->removeAllButtons();
                CRUD::addButton('line', 'restore', 'view', 'crud::buttons.restore');
                CRUD::addButton('line', 'delete_permanently', 'view', 'crud::buttons.delete_permanently');
              } );

        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return string
     */
    public function deletePermanently($id)
    {
        $this->crud->hasAccessOrFail('delete_permanently');
        
        $id = $this->crud->getCurrentEntryId() ?? $id;
        
        return $this->crud->query->onlyTrashed()->find($id)->forceDelete();
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  int  $id
     * @return string
     */
    public function restore($id)
    {
        $this->crud->hasAccessOrFail('restore');

        $id = $this->crud->getCurrentEntryId() ?? $id;
        
        return $this->crud->query->onlyTrashed()->find($id)->restore();
    }
}