<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../db/database.php";
require_once __DIR__ . "/../config/google_project.php";
require_once __DIR__ . "/../helpers/session_helpers.php";

class SessionController {

    private $db;

    function __construct($db = null) {

        session_start();

        if ($db === null) {
            $db = new Database();
        }

        $this->db = $db;
    }

    function newSession(array $params): void {

        $google = new GoogleProject();

        if (isset($params["request"]["code"])) {

            // Comming back from Google's sign in page

            $google->authenticateCode($params["request"]["code"]);

            if (!$google->isTokenValid()) {
                $this->goToRoot();
                return;
            }

            $user_info = $google->getUserInfo();

            $user = new User($this->db);

            if ($user->findByEmail($user_info->email)) {
                update_user_token($this->db, $google, $user);
            } else {
                create_new_user($this->db, $google, $user_info, $user);
            }

            $this->set($user, $google);
            $this->goToRoot();
            return;
        }

        if (isset($_SESSION["token"])) {

            $google->setAccessToken($_SESSION["token"]);

            if ($google->isTokenValid()) {
                // User already signed in
                $this->goToRoot();
                return;
            }

            // Delete old session data
            $this->destroy($params);
        }

        if (isset($params["request"]["admin"]) && $params["request"]["admin"] === "1") {
            $_SESSION["admin"] = "1";
            session_write_close();
        }

        $google->reqUserAuth();
    }

    function destroy(array $params): void {

        session_destroy();

        if ($params["verb"] === "delete") {
            $this->goToRoot();
        }
    }

    function check(): array {

        if (!isset($_SESSION["token"])) {
            return ["signed_in" => false];
        }

        $google = new GoogleProject();

        $google->setAccessToken($_SESSION["token"]);

        return ["signed_in" => $google->isTokenValid()];
    }

    private function set(User $user, GoogleProject $google): void {
        $_SESSION["user_id"] = $user->getId();
        $_SESSION["token"] = $google->getAccessToken();
    }

    private function goToRoot(): void {
        $root_path = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . "/youtube_stats";
        header("Location: " . filter_var($root_path, FILTER_SANITIZE_URL));
    }
}
