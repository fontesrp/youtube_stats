<?php

declare(strict_types=1);

require_once __DIR__ . "/../db/database.php";

class User {

    private $db;
    private $errors = [];

    private $id;
    private $oauth_provider;
    private $oauth_uid;
    private $first_name;
    private $last_name;
    private $email;
    private $gender;
    private $locale;
    private $picture;
    private $link;
    private $is_admin;
    private $created_at;
    private $updated_at;

    function __construct(Database $db, array $oauth = []) {

        $this->db = $db;

        if (!empty($oauth)) {
            $this->findByOAuth($oauth["provider"], $oauth["token"]);
        }
    }

    private function setParams(array $params): void {

        $props = [
            "id",
            "oauth_provider",
            "oauth_uid",
            "first_name",
            "last_name",
            "email",
            "gender",
            "locale",
            "picture",
            "link",
            "is_admin",
            "created_at",
            "updated_at"
        ];

        foreach ($props as $prop) {
            if (array_key_exists($prop, $params)) {
                $this->$prop = $params[$prop];
            }
        }

        if (is_string($this->oauth_uid)) {
            try {
                $this->oauth_uid = json_decode($this->oauth_uid, true);
            } catch (Exception $e) {
                $this->oauth_uid = [];
            }
        }
    }

    function getParams(): array {

        $props = [
            "id" => null,
            "oauth_provider" => null,
            "oauth_uid" => null,
            "first_name" => null,
            "last_name" => null,
            "email" => null,
            "gender" => null,
            "locale" => null,
            "picture" => null,
            "link" => null,
            "is_admin" => null,
            "created_at" => null,
            "updated_at" => null
        ];

        foreach ($props as $key => $value) {
            $props["key"] = $this->$key;
        }

        return $props;
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
                oauth_provider,
                oauth_uid,
                first_name,
                last_name,
                email,
                gender,
                locale,
                picture,
                link,
                is_admin,
                created_at,
                updated_at
            FROM
                users
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

    function findByOAuth(string $oauth_provider, string $oauth_uid): bool {

        return $this->find([
            ["col" => "oauth_provider", "value" => $oauth_provider],
            ["custom" => "oauth_uid = CAST(? AS JSON)", "value" => $oauth_uid]
        ]);
    }

    function findByEmail(string $email): bool {

        return $this->find([
            ["col" => "email", "value" => $email]
        ]);
    }

    function create(array $params): int {

        $this->setParams($params);

        $this->db->clear();

        $this->db->setSql("INSERT INTO users (
                oauth_provider,
                oauth_uid,
                first_name,
                last_name,
                email,
                gender,
                locale,
                picture,
                link,
                is_admin
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->setParams([
            ["value" => $this->oauth_provider],
            ["value" => $this->oauth_uid],
            ["value" => $this->first_name],
            ["value" => $this->last_name],
            ["value" => $this->email],
            ["value" => $this->gender],
            ["value" => $this->locale],
            ["value" => $this->picture],
            ["value" => $this->link],
            ["type" => "i", "value" => $this->is_admin]
        ]);

        $this->db->query();

        $this->id = $this->db->getInsertId();

        $this->errors = $this->db->getErrors();

        if (empty($this->errors)) {
            $this->findById($this->id);
        }

        return $this->id;
    }

    function getErrors() {
        return $this->errors;
    }

    function getId() {
        return $this->id;
    }

    function update(array $params): bool {

        $props = [
            ["name" => "oauth_provider", "type" => "s", "value" => null],
            ["name" => "oauth_uid", "type" => "s", "value" => null],
            ["name" => "first_name", "type" => "s", "value" => null],
            ["name" => "last_name", "type" => "s", "value" => null],
            ["name" => "email", "type" => "s", "value" => null],
            ["name" => "gender", "type" => "s", "value" => null],
            ["name" => "locale", "type" => "s", "value" => null],
            ["name" => "picture", "type" => "s", "value" => null],
            ["name" => "link", "type" => "s", "value" => null],
            ["name" => "is_admin", "type" => "i", "value" => null]
        ];

        $cols = [];

        foreach ($props as $column) {

            $name = $column["name"];

            if (array_key_exists($name, $params)) {
                $column["value"] = $params[$name];
                $cols[] = $column;
            }
        }

        $conds = [
            ["name" => "id", "type" => "s", "value" => $this->id]
        ];

        $sql = $this->db->generateUpdate([
            "table" => "users",
            "cols" => $cols,
            "conditions" => $conds
        ]);

        $this->db->setSql($sql);
        $this->db->setParams(array_merge($cols, $conds));

        return $this->db->query();
    }
}
