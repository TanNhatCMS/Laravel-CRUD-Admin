<?php

namespace Backpack\CRUD;

use Backpack\CRUD\Injectables\CheckUnique as CheckUnique;

class CrudInjectable
{
    use CheckUnique;

    protected $requiredInjectables = [];

    protected $name = null;
    protected $options = null;
    protected $controller = null;

    public function __construct($name, $controller, $options)
    {
        $this->name = $name;
        $this->options = $options;
        $this->controller = $controller;
    }

    public function with($injectables)
    {
        if (is_string($injectables)) {
            $this->requiredInjectables[] = 'with'.ucwords($injectables);
        } elseif (is_array($injectables)) {
            foreach ($injectables as $injectable) {
                $this->requiredInjectables = 'with'.ucwords($injectable);
            }
        }

        return $this->inject();
    }

    private function inject()
    {
        foreach ($this->requiredInjectables as $injectable) {
            $this->{$injectable}();
        }
    }

    public function __call($method, $parameters = null)
    {
        if (method_exists($this, $method)) {
            $this->{$method}($parameters);
        }
    }
}
