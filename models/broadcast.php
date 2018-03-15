<?php

declare(strict_types=1);

class Broadcast {

    protected $google;
    protected $youtube;

    protected $startAt;
    protected $endAt;
    protected $snippet;
    protected $status;
    protected $insert;
    protected $response;
    protected $streamSnippet;
    protected $cdn;
    protected $streamInsert;
    protected $streamResponse;
    protected $bindResponse;

    protected $id;
    protected $title;
    protected $publishedAt;

    protected $streamId;
    protected $streamTitle;

    protected $boundBroadcastId;
    protected $boundStreamId;

    function __construct(GoogleProject $google) {

        $this->google = $google;

        $this->youtube = new Google_Service_YouTube($google->getClient());
    }

    // Schedule dates and times for the broadcast to start and finish
    private function setSchedule(array $schedule): void {
        $this->startAt = $schedule["start_at"];
        $this->endAt = $schedule["end_at"];
    }

    // Create an object for the liveBroadcast resource's snippet. Specify values
    // for the snippet's title, scheduled start time, and scheduled end time.
    private function newSnippet(string $title): void {

        $this->snippet = new Google_Service_YouTube_LiveBroadcastSnippet();

        $this->snippet->setTitle($title);
        $this->snippet->setScheduledStartTime($this->startAt);
        $this->snippet->setScheduledEndTime($this->endAt);
    }

    // Create an object for the liveBroadcast resource's status, and set the
    // broadcast's status.
    private function setStatus(string $status): void {

        $this->status = new Google_Service_YouTube_LiveBroadcastStatus();

        $this->status->setPrivacyStatus($status);
    }

    // Create the API request that inserts the liveBroadcast resource.
    private function createInsertRequest(): void {
        $this->insert = new Google_Service_YouTube_LiveBroadcast();
        $this->insert->setSnippet($this->snippet);
        $this->insert->setStatus($this->status);
        $this->insert->setKind("youtube#liveBroadcast");
    }

    // Execute the request and return an object that contains information
    // about the new broadcast.
    private function insertBroadcast(): void {
        $this->response = $this->youtube->liveBroadcasts->insert(
            "snippet,status",
            $this->insert,
            []
        );
    }

    // Create an object for the liveStream resource's snippet. Specify a value
    // for the snippet's title.
    private function newStreamSnippet(string $title): void {
        $this->streamSnippet = new Google_Service_YouTube_LiveStreamSnippet();
        $this->streamSnippet->setTitle($title);
    }

    // Create an object for content distribution network details for the live
    // stream and specify the stream"s format and ingestion type.
    private function setCDN(string $format, string $ingestion): void {
        $this->cdn = new Google_Service_YouTube_CdnSettings();
        $this->cdn->setFormat($format);
        $this->cdn->setIngestionType($ingestion);
    }

    // Create the API request that inserts the liveStream resource.
    private function createStreamInsertRequest(): void {
        $this->streamInsert = new Google_Service_YouTube_LiveStream();
        $this->streamInsert->setSnippet($this->streamSnippet);
        $this->streamInsert->setCdn($this->cdn);
        $this->streamInsert->setKind("youtube#liveStream");
    }

    // Execute the request and return an object that contains information
    // about the new stream.
    private function insertStream(): void {
        $this->streamResponse = $this->youtube->liveStreams->insert("snippet,cdn", $this->streamInsert, []);
    }

    // Bind the broadcast to the live stream.
    private function bindStream(): void {
        $this->bindResponse = $this->youtube->liveBroadcasts->bind(
            $this->response["id"],
            "id,contentDetails",
            ["streamId" => $this->streamResponse["id"]]
        );
    }

    private function newBroadcast(array $props): void {
        $this->setSchedule($props["schedule"]);
        $this->newSnippet($props["title"]);
        $this->setStatus($props["status"]);
        $this->createInsertRequest();
        $this->insertBroadcast();
    }

    private function newStream(array $props): void {
        $this->newStreamSnippet($props["title"]);
        $this->setCDN($props["format"], $props["ingestion"]);
        $this->createStreamInsertRequest();
        $this->insertStream();
    }

    private function setProps(array $userProps, array $defaults): array {

        $props = [];

        foreach ($defaults as $key => $value) {

            if (!isset($userProps[$key])) {
                // Property not passed by the user
                $props[$key] = $value;
            } else if (is_array($value)) {
                // Set of properties passed by the user, needs to be solved recursevely
                $props[$key] = $this->setProps($userProps[$key], $value);
            } else {
                // Property passed by the user
                $props[$key] = $userProps[$key];
            }
        }

        return $props;
    }

    function create(array $userProps = []) {

        // Default brodcasts starts immediately, finishes in one hour, is named
        // "New Broadcast" and is public. The standard stream is called "New Stream and
        // is transmitted at 240p via rtmp"
        $defaults = [
            "schedule" => [
                "start_at" => gmdate("Y-m-d\\TH:i:s\\Z"),
                "end_at" => gmdate("Y-m-d\\TH:i:s\\Z", strtotime("1 hour"))
            ],
            "title" => "New Broadcast",
            "status" => "public",
            "stream" => [
                "id" => null,
                "title" => "New Stream",
                "format" => "240p",
                "ingestion" => "rtmp"
            ]
        ];

        $props = $this->setProps($userProps, $defaults);

        $this->newBroadcast($props);

        $this->id = $this->response["id"];
        $this->title = $this->response["snippet"]["title"];
        $this->publishedAt = $this->response["snippet"]["publishedAt"];

        if ($props["stream"]["id"] === null) {
            $this->newStream($props["stream"]);
            $this->streamId = $this->streamResponse["id"];
            $this->streamTitle = $this->streamResponse["snippet"]["title"];
        } else {
            $this->streamId = $props["stream"]["id"];
            $this->streamTitle = $props["stream"]["title"];
        }

        $this->bindStream();

        $this->boundBroadcastId = $this->bindResponse["id"];
        $this->boundStreamId = $this->bindResponse["contentDetails"]["boundStreamId"];

        return [
            "response" => $this->response,
            "streamResponse" => $this->streamResponse,
            "bindResponse" => $this->bindResponse
        ];
    }

    function listStreams() {

        $this->streamResponse = $this->youtube->liveStreams->listLiveStreams("id,snippet,cdn,status", [
            "mine" => true
        ]);

        return $this->streamResponse["items"];
    }

    function all() {

        $this->response = $this->youtube->liveBroadcasts->listLiveBroadcasts(
            "id,snippet,contentDetails,status",
            ["mine" => true]
        );

        return $this->response["items"];
    }

    function destroy() {
        return $this->youtube->liveBroadcasts->delete("XT7RAOxC7xE");
    }

    function messages(string $chatId) {

        $res = $this->youtube->liveChatMessages->listLiveChatMessages(
            $chatId,
            "id,snippet,authorDetails"
        );

        return $res["items"];
    }

    function newMessage($props) {

        $details = new Google_Service_YouTube_LiveChatTextMessageDetails();
        $details->setMessageText($props["body"]);

        $snippet = new Google_Service_YouTube_LiveChatMessageSnippet();
        $snippet->setType("textMessageEvent");
        $snippet->setLiveChatId($props["chat_id"]);
        $snippet->setTextMessageDetails($details);

        $message = new Google_Service_YouTube_LiveChatMessage();
        $message->setSnippet($snippet);

        $response = $this->youtube->liveChatMessages->insert("snippet", $message);

        return $response;
    }

    function findById(string $id) {

        $this->response = $this->youtube->liveBroadcasts->listLiveBroadcasts(
            "id,snippet,contentDetails,status",
            ["id" => $id]
        );

        return $this->response["items"];
    }
}
