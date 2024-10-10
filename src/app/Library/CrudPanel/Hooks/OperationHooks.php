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

    public array $hooks = [];

    public  function register(string $hook, string|array $operations, callable $callback): void
    {
        $operations = is_array($operations) ? $operations : [$operations];

        foreach ($operations as $operation) {
            $this->hooks[$operation][$hook] = $callback;
        }
    }

    public function run(string $hook, string|array $operations, array $parameters): void
    {
        $operations = is_array($operations) ? $operations : [$operations];
        foreach ($operations as $operation) {
            if (isset($this->hooks[$operation][$hook])) {
                $this->hooks[$operation][$hook](...$parameters);
            }
        }
    }

    public function has(string $hook, string $operation): bool
    {
        return isset($this->hooks[$operation][$hook]);
    }
}
