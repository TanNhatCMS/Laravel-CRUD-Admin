<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;

if (! backpack_pro()) {
    trait BulkTrashOperation
    {
        public function setupBulkTrashOperationDefaults()
        {
            throw new BackpackProRequiredException('BulkTrashOperation');
        }
    }
} else {
    trait BulkTrashOperation
    {
        use \Backpack\Pro\Http\Controllers\Operations\BulkTrashOperation;
    }
}
