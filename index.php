<?php

// Telegram Assistant Bot for telegram .

class ChatGPT
{
    private $openaiApiKey;
    private $telegramBotToken;
    private $gpt3ApiEndpoint;
    private $apiTelegram;

    public function __construct($telegramBotToken, $openaiApiKey)
    {
        $this->telegramBotToken = $telegramBotToken;
        $this->openaiApiKey = $openaiApiKey;
        $this->gpt3ApiEndpoint = 'https://api.openai.com/v1/chat/completions';
        $this->apiTelegram = "https://api.telegram.org/bot{$this->telegramBotToken}/";
    }

    public function sendMessage($userId, $text, $parse_mode = null)
    {
        $url = $this->apiTelegram . "sendMessage";
        $data = [
            'chat_id' => $userId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function processChat($conversation, $apiKey, $endpoint)
    {
        $data = [
            'model' => 'gpt-3.5-turbo-0613',
            'messages' => $conversation,
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            file_put_contents('curl_errors.txt', 'Curl Error: ' . curl_error($ch) . PHP_EOL, FILE_APPEND);
            return 'Error processing chat';
        } else {
            var_dump($response);
            var_dump(curl_getinfo($ch, CURLINFO_HTTP_CODE));

            $decodedResponse = json_decode($response, true);
            $generatedText = $decodedResponse['choices'][0]['message']['content'];
            return $generatedText;
        }

        curl_close($ch);
    }

    public function handleUpdate($update)
    {
        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $userMessage = $message['text'];
        $conversation = [
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant.',
            ],
            [
                'role' => 'user',
                'content' => $userMessage,
            ],
        ];

        $response = $this->processChat($conversation, $this->openaiApiKey, $this->gpt3ApiEndpoint);
        $this->sendMessage($chatId, $response);
    }
}

    $gpt3ApiEndpoint = 'https://api.openai.com/v1/chat/completions';
    $openaiApi = 'OPENAI_API_KEY';
    $botToken = "Telegram_API_KEY";
    $bot = new ChatGPT($botToken, $openaiApi);

    $update = json_decode(file_get_contents("php://input"), true);
    $bot->handleUpdate($update);
