<?php

spl_autoload_register(function ($class) {

    $projectRoot = 'App\\';
    $base_dir = __DIR__ . '/';

    $length = strlen($projectRoot);

    if (strncmp($projectRoot, $class, (int)$length) !== 0) {
        // Class does not use the namespace prefix, so move to next registered autoloader
        return;
    }

    $relative_class = substr($class, $length); // returns Config
    $file = $base_dir . str_replace('\\', '/', $projectRoot) . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

?>