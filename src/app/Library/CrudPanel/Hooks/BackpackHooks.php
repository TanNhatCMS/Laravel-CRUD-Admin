<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Hooks;

final class BackpackHooks
{
    public array $hooks = [];

    public static function register(string $hook, string|array $operations, callable $callback): void
    {
        $operations = is_array($operations) ? $operations : [$operations];

        foreach ($operations as $operation) {
            self::$hooks[$operation][$hook] = $callback;
        }
    }

    public static function run(string $hook, string $operation, array $parameters): void
    {
        if (isset(self::$operationHooks[$operation][$hook])) {
            self::$hooks[$operation][$hook](...$parameters);
        }
    }

    public static function has(string $hook, string $operation): bool
    {
        return isset(self::$hooks[$operation][$hook]);
    }
}
