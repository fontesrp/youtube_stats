<?php

/**
 * /routes/routes.php
 *
 * This file is responsible for defining the routes and their action. Each method
 * defines an endpoint. Only the method with the same name as the request's
 * endpoint is called. If any value is returned, it is sent to the client as
 * JSON. Methods should choose the appropriated controller action based ont he
 * request's parameters.
 *
 */

declare(strict_types=1);

require_once __DIR__ . "/router.php";

class Routes extends Router {

    protected $reqParams;

    function __construct($request) {

        parent::__construct($request);

        $this->reqParams = [
            "method" => $this->method,
            "endpoint" => $this->endpoint,
            "verb" => $this->verb,
            "args" => $this->args,
            "request" => $this->request
        ];
    }

    protected function root(): array {
        return [
            "path" => "/",
            "response" => "It works!"
        ];
    }

    protected function example(): array {
        return [
            "path" => "/example",
            "response" => "It works!"
        ];
    }
}