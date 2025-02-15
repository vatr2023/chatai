<?php

declare(strict_types=1);

class AIChatClient
{
    private const API_URL = "https://mistral-ai.chat/wp-admin/admin-ajax.php";
    private const NONCE = "83103efe99";
    private const USER_AGENT = "Mozilla/5.0 (Linux; Android 13; Mobile) AppleWebKit/537.36 "
        . "(KHTML, like Gecko) Chrome/132.0.0.0 Mobile Safari/537.36";
    
    private string $message;
    private array $headers;

    public function __construct(string $message)
    {
        $this->message = $message;
        $this->headers = [
            "User-Agent: " . self::USER_AGENT,
            "Accept: application/json",
            "x-requested-with: XMLHttpRequest",
            "referer: https://mistral-ai.chat/"
        ];
    }

    public function sendRequest(): string
    {
        $postData = http_build_query([
            'action'  => "ai_chat_response",
            'message' => $this->message,
            'nonce'   => self::NONCE
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::API_URL,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $this->headers
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        return $response !== false ? $response : json_encode(["error" => "Request failed: $error"]);
    }
}

// Set JSON response header
header("Content-Type: application/json");

// Validate input
if (!isset($_GET['message']) || empty(trim($_GET['message']))) {
    echo json_encode(["error" => "Message is required"]);
    exit;
}

// Process request
$client = new AIChatClient(trim($_GET['message']));
echo $client->sendRequest();
