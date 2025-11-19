<?php
spl_autoload_register(function ($class) {
    $prefix = 'Core\\';
    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $path = __DIR__ . '/src/Core/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($path)) {
            require $path;
        }
        return;
    }
    if (str_starts_with($class, 'Modules\\')) {
        $relative = substr($class, strlen('Modules\\'));
        $path = __DIR__ . '/src/Modules/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($path)) {
            require $path;
        }
    }
});

require_once __DIR__ . '/vendor/autoload.php';
