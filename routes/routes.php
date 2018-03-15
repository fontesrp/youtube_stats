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
require_once __DIR__ . "/../controllers/session_controller.php";
require_once __DIR__ . "/../controllers/broadcasts_controller.php";
require_once __DIR__ . "/../controllers/users_controller.php";
require_once __DIR__ . "/../controllers/messages_controller.php";

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

    protected function root() {
        require_once __DIR__ . "/../public/index.html";
    }

    protected function session() {

        $session_controller = new SessionController();

        switch ($this->verb) {

        case "new":

            if ($this->method === "GET") {
                $session_controller->newSession($this->reqParams);
                return;
            }

            break;

        case "delete":

            if ($this->method === "GET") {
                $session_controller->destroy($this->reqParams);
                return;
            }

            break;

        case "check":

            if ($this->method === "GET") {
                return $session_controller->check();
            }

            break;
        }

        return ["error" => "invalid request"];
    }

    protected function broadcasts() {

        $broadcasts_controller = new BroadcastsController();

        switch ($this->verb) {

        case "streams":

            if ($this->method === "GET") {
                return $broadcasts_controller->streams();
            }

            break;

        case "show":

            if ($this->method === "GET") {
                return $broadcasts_controller->show($this->reqParams);
            }

            break;

        case "messages":

            switch ($this->method) {
            case "GET":
                return $broadcasts_controller->messages($this->reqParams);
            case "POST":
                return $broadcasts_controller->newMessage($this->reqParams);
            }

            break;

        default:

            switch ($this->method) {
            case "GET":
                return $broadcasts_controller->index();
            case "POST":
                return $broadcasts_controller->create($this->reqParams);
            }

            break;
        }

        return ["error" => "invalid request"];
    }

    protected function users() {

        $user_controller = new UserController();

        switch ($this->verb) {
        case "messages":

            if ($this->method === "GET") {
                return $user_controller->messages($this->reqParams);
            }

            break;

        case "all_with_messages":

            if ($this->method === "GET") {
                return $user_controller->allWithMessages($this->reqParams);
            }

            break;
        }

        return ["error" => "invalid request"];
    }

    protected function messages() {

        $messages_controller = new MessagesController();

        if ($this->verb === "report" && $this->method === "GET") {
            return $messages_controller->report($this->reqParams);
        }

        return ["error" => "invalid request"];
    }
}
