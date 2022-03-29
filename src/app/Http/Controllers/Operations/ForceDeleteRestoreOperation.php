<?php

namespace Siberfx\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait ForceDeleteRestoreOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupForceDeleteRestoreRoutes($segment, $routeName, $controller)
    {
        Route::delete($segment.'/{id}/forceDelete', [
            'as'        => $routeName.'.forceDelete',
            'uses'      => $controller.'@forceDelete',
            'operation' => 'forceDelete',
        ]);

        Route::post($segment.'/{id}/restore', [
            'as'        => $routeName.'.restore',
            'uses'      => $controller.'@restore',
            'operation' => 'restore',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupForceDeleteDefaults()
    {
        $this->crud->allowAccess('forceDelete');
        $this->crud->allowAccess('restore');

        $this->crud->operation('forceDelete', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation('list', function () {
            $this->crud->addButton('line', 'restore', 'view', 'crud::buttons.restore', 'end');
            $this->crud->addButton('line', 'force-delete', 'view', 'crud::buttons.force-delete', 'end');

        });
    }

    /**
     * @param $id
     * @return mixed
     */
    public function forceDelete($id)
    {
        $this->crud->hasAccessOrFail('forceDelete');

        return $this->crud->model->withTrashed()->find($id)->forceDelete();

    }


    /**
     * @param $id
     * @return mixed
     */
    public function restore($id)
    {
        $this->crud->hasAccessOrFail('restore');

        return $this->crud->model->withTrashed()->find($id)->restore();

    }
}
