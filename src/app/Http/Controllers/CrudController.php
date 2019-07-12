<?php

namespace Backpack\CRUD\app\Http\Controllers;

use ReflectionMethod;
use Backpack\CRUD\CrudPanel;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Backpack\CRUD\app\Http\Controllers\Operations\SaveActions;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\RevisionsOperation;

class CrudController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;
    use CreateOperation, CloneOperation, DeleteOperation, ListOperation, ReorderOperation, RevisionsOperation, SaveActions, ShowOperation, UpdateOperation;

    public $data = [];
    public $request;

    /**
     * @var CrudPanel
     */
    public $crud;

    public function __construct()
    {
        if (! $this->crud) {
            $this->crud = app()->make(CrudPanel::class);

            // call the setup function inside this closure to also have the request there
            // this way, developers can use things stored in session (auth variables, etc)
            $this->middleware(function ($request, $next) {
                $this->request = $request;
                $this->crud->request = $request;
                $this->setup();

                return $next($request);
            });
        }
    }

    public function callPublicFunction(...$params) {
        $parameters = func_get_args()[0];
        $httpVerb = \Request::method();
        $input = \Request::input();
        $functionName = explode('/', $parameters)[0];
        $functionExists = method_exists($this, $functionName);
        $functionIsPublic = false;

        if ($functionExists) {
            $reflection = new ReflectionMethod($this, $functionName);
            $functionIsPublic = $reflection->isPublic();
        }

        if ($functionIsPublic) {
            return $this->{$functionName}(...$params);
        }
    }

    /**
     * Allow developers to set their configuration options for a CrudPanel.
     */
    public function setup()
    {
    }
}
