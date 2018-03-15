<?php

declare(strict_types=1);

require_once __DIR__ . "/../db/database.php";

class Message {

    private $db;
    private $errors = [];

    private $id;
    private $body;
    private $user_id;
    private $chat_id;
    private $created_at;
    private $updated_at;


    function __construct(Database $db) {
        $this->db = $db;
    }

    private function setParams(array $params): void {

        $props = [
            "id",
            "body",
            "user_id",
            "chat_id",
            "created_at",
            "updated_at"
        ];

        foreach ($props as $prop) {
            if (array_key_exists($prop, $params)) {
                $this->$prop = $params[$prop];
            }
        }
    }

    private function filter(array $params): string {

        $conditions = array_map(function ($ftr) {

            if (array_key_exists("custom", $ftr)) {
                return $ftr["custom"];
            }

            $compare = (array_key_exists("compare", $ftr))
                ? $ftr["compare"]
                : "=";

            return $ftr["col"] . " " . $compare . " ?";
        }, $params);

        return implode(" AND ", $conditions);
    }

    function find(array $params): bool {

        $this->db->clear();

        $this->db->setSql("SELECT
                id,
                body,
                user_id,
                chat_id,
                created_at,
                updated_at
            FROM
                messages
            WHERE " . $this->filter($params));

        $this->db->setParams($params);

        $this->db->query();

        if ($row = $this->db->getRow()) {

            $this->setParams($row);

            return true;
        }

        return false;
    }

    function findById(int $id): bool {

        return $this->find([
            ["col" => "id", "type" => "i", "value" => $id]
        ]);
    }

    function create(array $params): int {

        $this->setParams($params);

        $this->db->clear();

        $this->db->setSql("INSERT INTO messages (
                body,
                user_id,
                chat_id
            )
            VALUES (?, ?, ?)");

        $this->db->setParams([
            ["value" => $this->body],
            ["type" => "i", "value" => $this->user_id],
            ["value" => $this->chat_id]
        ]);

        $this->db->query();

        $this->id = $this->db->getInsertId();

        $this->errors = $this->db->getErrors();

        if (empty($this->errors)) {
            $this->findById($this->id);
        }

        return $this->id;
    }
}
