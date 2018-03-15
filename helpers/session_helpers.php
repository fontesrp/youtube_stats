<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../db/database.php";
require_once __DIR__ . "/../config/google_project.php";
require_once __DIR__ . "/../models/user.php";

function get_curr_user($db = null, $google = null) {

    if (!isset($_SESSION["token"])) {
        return null;
    }

    if ($db === null) {
        $db = new Database();
    }

    if ($google === null) {
        $google = new GoogleProject();
        $google->setAccessToken($_SESSION["token"]);
    }

    if (!$google->isTokenValid()) {
        return null;
    }

    $user = new User($db, ["provider" => "google", "token" => json_encode($google->getAccessToken())]);

    if ($user->getId() === null) {
        return null;
    }

    return $user;
}

function is_admin(): int {
    return (isset($_SESSION["admin"]) && $_SESSION["admin"] === "1")
        ? 1
        : 0;
}

function update_user_token(Database $db, GoogleProject $google, $user = null): bool {

    if ($user === null) {
        $user = get_curr_user($db, $google);
    }

    if ($google->isTokenValid()) {

        $user->update([
            "oauth_provider" => "google",
            "oauth_uid" => json_encode($google->getAccessToken()),
            "is_admin" => is_admin()
        ]);

        return true;
    }

    return false;
}

function create_new_user(Database $db, GoogleProject $google, $user_info, $user = null): void {

    if ($user === null) {
        $user = new User($db);
    }

    $user->create([
        "oauth_provider" => "google",
        "oauth_uid" => json_encode($google->getAccessToken()),
        "first_name" => $user_info->givenName,
        "last_name" => $user_info->familyName,
        "email" => $user_info->email,
        "gender" => $user_info->gender,
        "locale" => $user_info->locale,
        "picture" => $user_info->picture,
        "link" => $user_info->link,
        "is_admin" => is_admin()
    ]);
}

function user_signed_in(): bool {

    if (empty($_SESSION)) {
        session_start();
    }

    if (!isset($_SESSION["token"])) {
        return false;
    }

    $google = new GoogleProject();
    $google->setAccessToken($_SESSION["token"]);

    return $google->isTokenValid();
}
