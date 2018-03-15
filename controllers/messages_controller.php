<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../db/database.php";
require_once __DIR__ . "/../config/google_project.php";
require_once __DIR__ . "/../models/message.php";

class MessagesController {

    protected $db;
    protected $message;

    function __construct($db = null) {

        if ($db === null) {
            $db = new Database();
        }

        $this->db = $db;

        $this->message = new Message($this->db);
    }

    function report(array $params): array {

        $chat_id = $params["request"]["chat_id"];

        return [
            "hype" => $this->message->currentHype($chat_id),
            "hist" => $this->message->histHype($chat_id)
        ];
    }
}
