<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

if (! backpack_pro()) {
    trait TrashOperation
    {
        public function setupTrashOperationDefaults()
        {
            throw new BackpackProRequiredException('TrashOperation');
        }
    }
} else {
    trait TrashOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\TrashOperation;
    }
}
