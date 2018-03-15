<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../db/database.php";
require_once __DIR__ . "/../models/user.php";
require_once __DIR__ . "/../models/message.php";

class UserController {

    private $db;
    private $user;

    function __construct($db = null) {

        if ($db === null) {
            $db = new Database();
        }

        $this->db = $db;

        $this->user = new User($this->db);
    }

    function messages(array $params) {

        $message = new Message($this->db);

        if (isset($params["request"]["id"])) {
            $user_id = (int) $params["request"]["id"];
        } else {

            $email = (string) $params["request"]["email"];

            $user = new User($this->db);
            $user->findByEmail($email);

            $user_id = $user->getId();
        }

        return $message->allByUser($user_id);
    }
}
