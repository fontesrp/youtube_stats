<?php

/**
 * /db/database.php
 *
 * This is a database abstraction layer. It provides useful functions to avoid
 * working directly with the mysqli API. To instanciate the Database class, the
 * global constants for the session must be set. Otherwise an exception will be
 * thrown. This is a necessary protection step to allow the live demo of the
 * application.
 *
 */

declare(strict_types=1);

class Database {

    private $connector = null;
    private $sql = "";
    private $params = [];
    private $response = null;
    private $errors = [];

    function __construct() {

        if (
            !defined("DB_HOST")
            || !defined("DB_USERNAME")
            || !defined("DB_PASSWORD")
            || !defined("DB_DBNAME")
        ) {
            throw new Exception("Failed to connect to MySQL: invalid session constants", 1);
        }

        $this->connector = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DBNAME);

        if ($this->connector->connect_error) {
            throw new Exception("Failed to connect to MySQL: (" . $this->connector . ") " . $this->connector->connect_error, 1);
        }
    }

    function clear(): void {
        $this->sql = "";
        $this->params = [];
        $this->response = null;
        $this->errors = [];
    }

    function setSql(string $sql): void {
        $this->sql = $sql;
    }

    function getSql(): string {
        return $this->sql;
    }

    function setParams(array $params): void {
        $this->params = $params;
    }

    function getParams(): array {
        return $this->params;
    }

    function query(): bool {

        $statement = $this->connector->prepare($this->sql);

        if (!empty($this->params)) {
            $params = $this->parseParams();
            $statement->bind_param($params["types"], ...$params["values"]);
        }

        $result = $statement->execute();

        $this->errors = $statement->error_list;
        $this->response = $statement->get_result();

        $statement->close();

        return $result;
    }

    private function parseParams(): array {

        $parse = [
            "types" => "",
            "values" => []
        ];

        foreach ($this->params as $param) {

            $parse["types"] .= (array_key_exists("type", $param))
                ? $param["type"]
                : "s";

            $parse["values"][] = $param["value"];
        }

        return $parse;
    }

    /**
     * @return mixed (either null or an array)
     */
    function getRow() {
        return $this->response->fetch_assoc();
    }

    function getAll(): array {
        return $this->response->fetch_all(MYSQLI_ASSOC);
    }

    function getInsertId(): int {
        return $this->connector->insert_id;
    }

    function getErrors(): array {
        return $this->errors;
    }

    function generateUpdate(array $params): string {

        $colVals = "";

        foreach ($params["cols"] as $column) {

            if ($colVals !== "") {
                $colVals .= ", ";
            }

            $colVals .= $column["name"] . " = ?";
        }

        $conds = "";

        foreach ($params["conditions"] as $condition) {

            if ($conds !== "") {

                if (!array_key_exists("cond_type", $condition)) {
                    $condition["cond_type"] = "AND";
                }

                $conds .= " " . $condition["cond_type"] . " ";
            }

            $conds .= $condition["name"] . " = ?";
        }

        return "UPDATE " . $params["table"] . " SET " . $colVals . " WHERE " . $conds;
    }
}
