<?php

/**
 * /routes/router.php
 *
 * This file is responsible for reading and responding to the requests recieved
 * by the server. All request parameters are standardized in the class's
 * properties, allowing the Controllers to access them without worrying about the
 * request method or the form in wich the data was passed.
 *
 */

declare(strict_types=1);

abstract class Router {

    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = "";

    /**
     * Property: endpoint
     * The Controller requested in the URI. eg: /files
     */
    protected $endpoint = "";

    /**
     * Property: verb
     * An optional additional descriptor about the endpoint, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    protected $verb = "";

    /**
     * Property: args
     * Any additional URI components after the endpoint and verb have been removed, in our
     * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
     * or /<endpoint>/<arg0>
     */
    protected $args = [];

    /**
     * Property: request
     * Stores the form data from the request
     */
    protected $request = [];

    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     */
    function __construct(string $request) {

        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");

        $query = "?" . $_SERVER["QUERY_STRING"];
        $request = str_replace($query, "", $request);

        $this->args = explode("/", trim($request, "/"));

        // Remove the app's name
        array_shift($this->args);

        // Define the controller
        if (empty($this->args)) {
            $this->endpoint = "root";
        } else {
            $this->endpoint = array_shift($this->args);
        }

        if (!empty($this->args) && !is_numeric($this->args[0])) {
            $this->verb = array_shift($this->args);
        }

        $this->method = $_SERVER["REQUEST_METHOD"];

        switch($this->method) {

        case "GET":

            $this->request = $this->_cleanInputs($_GET);

            break;

        case "DELETE":
        case "POST":

            $request = $this->_getJsonBody();

            if (empty($request)) {
                $request = $_POST;
            }

            $this->request = $this->_cleanInputs($request);

            break;

        case "PUT":
        case "PATCH":

            $request = $this->_getJsonBody();
            $this->request = $this->_cleanInputs($request);

            break;

        default:
            $this->_response("Invalid Method", 405);
            break;
        }
    }

    function processRequest() {

        if (method_exists($this, $this->endpoint)) {

            $resp = $this->{$this->endpoint}($this->args);

            return ($resp === null)
                ? null
                : $this->_response($resp);
        }

        return $this->_response("No Endpoint: " . $this->endpoint, 404);
    }

    private function _response($data, int $status = 200): string {

        header("Content-Type: application/json");
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));

        return json_encode($data);
    }

    private function _cleanInputs($data) {

        $clean_input = [];

        if ($data === null) {
            $clean_input = null;
        } else if (is_array($data)) {
            foreach ($data as $key => $value) {
                $clean_input[$key] = $this->_cleanInputs($value);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }

        return $clean_input;
    }

    private function _requestStatus(int $code): string {

        $status = [
            200 => "OK",
            404 => "Not Found",
            405 => "Method Not Allowed",
            500 => "Internal Server Error",
        ];

        return (array_key_exists($code, $status))
            ? $status[$code]
            : $status[500];
    }

    private function _getJsonBody() {

        $request_body = file_get_contents("php://input");

        try {
            $request = json_decode(urldecode($request_body), true);
        } catch (Exception $e) {
            $request = [];
        }

        return $request;
    }
}
