<?php

use Illuminate\Support\Facades\Route;

it('resolves every routed controller action to a real, callable method', function () {
    $failures = [];

    foreach (Route::getRoutes() as $route) {
        $controllerAction = $route->getAction('controller');

        if (! $controllerAction || ! str_contains($controllerAction, '@')) {
            continue; // closure route — nothing to resolve
        }

        [$class, $method] = explode('@', $controllerAction, 2);

        if (! class_exists($class)) {
            $failures[] = "{$route->uri()} -> {$class}@{$method} (class does not exist)";
            continue;
        }

        if (! method_exists($class, $method)) {
            $failures[] = "{$route->uri()} -> {$class}@{$method} (method does not exist)";
        }
    }

    expect($failures)->toBe([]);
});
