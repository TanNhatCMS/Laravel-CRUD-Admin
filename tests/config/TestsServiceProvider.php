<?php

namespace Backpack\CRUD\Tests\Config;

class TestsServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->loadViewsFrom(__DIR__.'/Views', 'backpack.theme-coreuiv2');

        // Register the  facade alias for basset
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Basset', \Backpack\Basset\Facades\Basset::class);
        $loader->alias('Alert', \Prologue\Alerts\Facades\Alert::class);
        $loader->alias('CRUD', \Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade::class);
    }
}
