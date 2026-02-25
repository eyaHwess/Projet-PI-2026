<?php

// Doit être appelé en tout premier (avant tout require) pour éviter le timeout 30s sous PHP-CGI
set_time_limit(300);

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
