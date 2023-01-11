<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait DeleteOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupDeleteRoutes($segment, $routeName, $controller)
    {
        Route::delete($segment.'/{id}', [
            'as'        => $routeName.'.destroy',
            'uses'      => $controller.'@destroy',
            'operation' => 'delete',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupDeleteDefaults()
    {
        $this->crud->allowAccess('delete');

        $this->crud->operation('delete', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        if (! in_array('Backpack\Pro\Http\Controllers\Operations\TrashOperation', class_uses_recursive($this))) {
            $this->crud->operation(['list', 'show'], function () {
                $this->crud->addButton('line', 'delete', 'view', 'crud::buttons.delete', 'end');
            });
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return string
     */
    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        $usingSoftDeletes = $this->crud->model->isSoftDeleted() && 
                            (in_array(TrashOperation::class, class_uses_recursive($this)) ||
                            in_array(BulkTrashOperation::class, class_uses_recursive($this)));

        if ($usingSoftDeletes) {
            return $this->crud->query->onlyTrashed()->findOrFail($id)->forceDelete();
        }

        return $this->crud->query->findOrFail($id)->delete();
    }
}
