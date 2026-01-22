<?php

declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use App\Application\Middleware\UserContextMiddleware;
use Slim\App;

return function (App $app): void {

    // 1) Routing middleware (ZORUNLU)
    $app->addRoutingMiddleware();

    // 2) Body parsing (JSON POST/PUT/PATCH icin ZORUNLU)
    $app->addBodyParsingMiddleware();

    // 3) Session (opsiyonel ama kalsin)
    $app->add(SessionMiddleware::class);

    // 4) User context (X-User-Name burada kontrol ediliyor)
    $app->add(UserContextMiddleware::class);

    // 5) Error middleware (istersen ac)
    // $app->addErrorMiddleware(true, true, true);
};
