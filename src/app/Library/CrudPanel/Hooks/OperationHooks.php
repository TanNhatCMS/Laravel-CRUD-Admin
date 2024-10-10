<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Hooks;

final class OperationHooks
{
    const BEFORE_OPERATION_SETUP_ROUTES = 'beforeOperationSetupRoutes';

    const AFTER_OPERATION_SETUP_ROUTES = 'afterOperationSetupRoutes';

    const BEFORE_OPERATION_SETUP_DEFAULTS = 'beforeOperationSetupDefaults';

    const AFTER_OPERATION_SETUP_DEFAULTS = 'afterOperationSetupDefaults';

    const BEFORE_CONTROLLER_SETUP = 'beforeControllerSetup';

    const AFTER_CONTROLLER_SETUP = 'afterControllerSetup';

    const BEFORE_OPERATION_SETUP = 'beforeOperationSetup';

    const AFTER_OPERATION_SETUP = 'afterOperationSetup';

    const SETUP_OPERATION_FROM_CONFIG = 'setupOperationFromConfig';
}
