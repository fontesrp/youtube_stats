<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

class GoogleProject {

    protected $clientSecretsPath = "/var/www/client_secrets.json";
    protected $client;
    protected $accessToken = [];
    protected $oauth2;

    function __construct($client = null) {

        if (is_a($client, "Google_Client")) {
            $this->client = $client;
            return;
        }

        $this->client = new Google_Client();

        // Location of the client_secrets.json file
        $this->client->setAuthConfigFile($this->clientSecretsPath);

        // Where the client is redirected to after authorization. This has to match the
        // URL on Google's developer console
        $new_session_path = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . "/youtube_stats/session/new";
        $this->client->setRedirectUri(filter_var($new_session_path, FILTER_SANITIZE_URL));

        $this->client->addScope("https://www.googleapis.com/auth/userinfo.email");
        $this->client->addScope("https://www.googleapis.com/auth/userinfo.profile");
        $this->client->addScope(Google_Service_YouTube::YOUTUBE);
        $this->client->setAccessType("offline");
        $this->client->setIncludeGrantedScopes(true);
    }

    function getClient() {
        return $this->client;
    }

    function reqUserAuth(): void {

        $auth_url = $this->client->createAuthUrl();

        // Redirect client to Google's authorization page
        header("Location: " . filter_var($auth_url, FILTER_SANITIZE_URL));
    }

    function setAccessToken(array $token = []): void {

        if (!empty($token)) {
            $this->accessToken = $token;
        }

        $this->client->setAccessToken($this->accessToken);
    }

    // function refreshToken(): void {
    //     if ($this->client->isAccessTokenExpired()) {
    //         $this->client->refreshToken($this->client->getRefreshToken());
    //         $token = $this->client->getAccessToken();
    //         $this->accessToken = $token["access_token"];
    //     }
    // }

    function getAccessToken(): array {

        $token = $this->accessToken;

        return ["access_token" => $token["access_token"], "created" => $token["created"]];
    }

    function authenticateCode(string $auth_code): void {

        $this->client->authenticate($auth_code);

        $this->setAccessToken($this->client->getAccessToken());
    }

    function isTokenValid(): bool {

        $this->oauth2 = new Google_Service_Oauth2($this->client);

        try {
            $token = $this->client->getAccessToken();
            $info = $this->oauth2->tokeninfo(["access_token" => $token["access_token"]]);
        } catch (Exception $e) {
            $info = "error";
        }

        return ($info !== "error");
    }

    function getUserInfo() {

        if (!$this->isTokenValid()) {
            return [];
        }

        return $this->oauth2->userinfo->get();
    }
}

