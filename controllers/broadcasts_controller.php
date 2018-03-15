<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../db/database.php";
require_once __DIR__ . "/../config/google_project.php";
require_once __DIR__ . "/../models/user.php";
require_once __DIR__ . "/../models/broadcast.php";

class BroadcastsController {

    protected $db;
    protected $google;

    function __construct($db = null) {

        if ($db === null) {
            $db = new Database();
        }

        $this->db = $db;

        $this->google = new GoogleProject();
    }

    function index() {

        $user = new User($this->db);

        $admins = $user->allAdmins();
        $broadcasts = [];

        foreach ($admins as $adm) {

            $this->google->setAccessToken($adm["oauth_uid"]);

            if (!$this->google->isTokenValid()) {
                continue;
            }

            $bc = new Broadcast($this->google);
            $user_bcs = $bc->all();

            if (!empty($user_bcs)) {
                $broadcasts[$adm["id"]] = $user_bcs;
            }
        }

        return $broadcasts;
    }

    function create(array $params) {

        session_start();

        $this->google->setAccessToken($_SESSION["token"]);

        $bc = new Broadcast($this->google);

        return $bc->create($params["request"]);
    }

    function streams() {

        session_start();

        $this->google->setAccessToken($_SESSION["token"]);

        if (!$this->google->isTokenValid()) {
            return [];
        }

        $bc = new Broadcast($this->google);

        return $bc->listStreams();
    }

    function show($params) {

        $user = new User($this->db);
        $user->findById((int) $params["request"]["owner"]);

        $this->google->setAccessToken($user->getOauthUid());

        if ($this->google->isTokenValid()) {

            $bc = new Broadcast($this->google);

            return $bc->findById($params["request"]["id"]);
        }

        return [];
    }
}
