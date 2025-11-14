<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    // Safe: log the current environment to a temporary file
    file_put_contents(__DIR__.'/../var/log/runtime_env.log', date('Y-m-d H:i:s') . " - ENV: " . $context['APP_ENV'] . "\n", FILE_APPEND);

    // Safe: optionally add a custom context variable
    if (!isset($context['APP_CUSTOM'])) {
        $context['APP_CUSTOM'] = 'default_value';
    }

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
