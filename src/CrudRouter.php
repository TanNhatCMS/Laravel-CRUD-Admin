<?php

namespace Backpack\CRUD;

use Route;

class CrudRouter
{
    protected $extraRoutes = [];

    protected $path = null;
    protected $name = null;
    protected $options = null;
    protected $controller = null;

    public function __construct($name, $controller, $options)
    {
        $this->name = $name;
        $this->controller = $controller;
        $this->options = $options;
        $this->path = $this->checkNameForNestedResources($name);

        // CRUD routes for core features
        Route::post($this->path.'/search', [
            'as' => 'crud.'.$this->name.'.search',
            'uses' => $this->controller.'@search',
        ]);

        Route::delete($this->path.'/bulk-delete', [
            'as' => 'crud.'.$this->name.'.bulkDelete',
            'uses' => $this->controller.'@bulkDelete',
        ]);

        Route::get($this->path.'/reorder', [
            'as' => 'crud.'.$this->name.'.reorder',
            'uses' => $this->controller.'@reorder',
        ]);

        Route::post($this->path.'/reorder', [
            'as' => 'crud.'.$this->name.'.save.reorder',
            'uses' => $this->controller.'@saveReorder',
        ]);

        Route::get($this->path.'/{id}/details', [
            'as' => 'crud.'.$this->name.'.showDetailsRow',
            'uses' => $this->controller.'@showDetailsRow',
        ]);

        Route::get($this->path.'/{id}/translate/{lang}', [
            'as' => 'crud.'.$this->name.'.translateItem',
            'uses' => $this->controller.'@translateItem',
        ]);

        Route::get($this->path.'/{id}/revisions', [
            'as' => 'crud.'.$this->name.'.listRevisions',
            'uses' => $this->controller.'@listRevisions',
        ]);

        Route::post($this->path.'/{id}/revisions/{revisionId}/restore', [
            'as' => 'crud.'.$this->name.'.restoreRevision',
            'uses' => $this->controller.'@restoreRevision',
        ]);

        Route::post($this->path.'/{id}/clone', [
            'as' => 'crud.'.$this->name.'.clone',
            'uses' => $this->controller.'@clone',
        ]);

        Route::post($this->path.'/bulk-clone', [
            'as' => 'crud.'.$this->name.'.bulkClone',
            'uses' => $this->controller.'@bulkClone',
        ]);
    }

    /**
     * The CRUD resource needs to be registered after all the other routes.
     */
    public function __destruct()
    {
        $options_with_default_route_names = array_merge([
            'names' => [
                'index' => 'crud.'.$this->name.'.index',
                'create' => 'crud.'.$this->name.'.create',
                'store' => 'crud.'.$this->name.'.store',
                'edit' => 'crud.'.$this->name.'.edit',
                'update' => 'crud.'.$this->name.'.update',
                'show' => 'crud.'.$this->name.'.show',
                'destroy' => 'crud.'.$this->name.'.destroy',
            ],
        ], $this->options);

        Route::resource($this->name, $this->controller, $options_with_default_route_names);
    }

    public function __call($method, $parameters = null)
    {
        if (method_exists($this, $method)) {
            $this->{$method}($parameters);
        }
    }

    /**
     * Call other methods in this class, that register extra routes.
     *
     * @param callable $injectables
     *
     * @return null
     */
    public function with($injectables)
    {
        if (is_string($injectables)) {
            $this->extraRoutes[] = 'with'.ucwords($injectables);
        } elseif (is_array($injectables)) {
            foreach ($injectables as $injectable) {
                $this->extraRoutes[] = 'with'.ucwords($injectable);
            }
        } else {
            $reflection = new \ReflectionFunction($injectables);

            if ($reflection->isClosure()) {
                $this->extraRoutes[] = $injectables;
            }
        }

        return $this->registerExtraRoutes();
    }

    /**
     * Check the "name" acually the path for the route
     * if it contains . for nested resource names.
     *
     * @return string
     */
    public function checkNameForNestedResources(string $name): string
    {
        if (! str_contains($name, '.')) {
            return $name;
        }

        $path = [];
        $parts = explode('.', $this->name);
        $except = end($parts);

        foreach ($parts as $part) {
            if ($part === $except) {
                $path[] = $part;
                continue;
            }

            $path[] = $part.'/{'.str_singular($part).'_id}';
        }

        return implode('/', $path);
    }

    /**
     * TODO
     * Give developers the ability to unregister a route.
     */
    // public function without($injectables) {}

    /**
     * Register the routes that were passed using the "with" syntax.
     */
    private function registerExtraRoutes()
    {
        foreach ($this->extraRoutes as $route) {
            if (is_string($route)) {
                $this->{$route}();
            } else {
                $route();
            }
        }
    }
}
