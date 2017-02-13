<?php

namespace Backpack\CRUD\Injectables;

use Route;

trait CheckUnique
{
    public function withCheckUniqueString()
    {
        Route::get($this->name.'/ajax/checkUniqueString', [
            'as' => 'crud.'.$this->name.'.checkUniqueString',
            'uses' => $this->controller.'@checkUniqueString',
        ]);

        Route::post($this->name.'/ajax/checkUniqueString', [
            'as' => 'crud.'.$this->name.'.checkUniqueString',
            'uses' => $this->controller.'@checkUniqueString',
        ]);

        return $this;
    }
}
