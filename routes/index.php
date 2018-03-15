<?php

/**
 * /routes/index.php
 *
 * Apache will redirect all requests to this file. It passes the request to the
 * Routes and, if there is any response, prints it.
 *
 */

declare(strict_types=1);

require_once __DIR__ . "/routes.php";

try {

    $routes = new Routes($_SERVER["REQUEST_URI"]);
    $resp = $routes->processRequest();

    if ($resp !== null) {
        echo $resp;
    }
} catch (Exception $err) {
    echo json_encode(["error" => $err->getMessage()]);
}
