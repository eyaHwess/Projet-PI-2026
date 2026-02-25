<?php
require 'vendor/autoload.php';

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->loadEnv(__DIR__ . '/.env');

$apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
$useMock = $_ENV['OPENAI_USE_MOCK'] ?? '1';

echo "OPENAI_USE_MOCK: $useMock\n";
echo "OPENAI_API_KEY: " . (strlen($apiKey) > 0 ? substr($apiKey, 0, 12) . '...' . substr($apiKey, -4) : 'EMPTY') . "\n";

if (empty($apiKey) || $apiKey === 'your_key_here') {
    echo "ERROR: API key is empty!\n";
    exit(1);
}

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'user', 'content' => 'Say "OK" only.'],
        ],
        'max_tokens' => 10,
    ]),
    CURLOPT_TIMEOUT => 20,
]);

$body = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
$decoded = json_decode($body, true);
if ($httpCode === 200) {
    echo "API Response: " . ($decoded['choices'][0]['message']['content'] ?? 'no content') . "\n";
    echo "SUCCESS: OpenAI API is working!\n";
} else {
    echo "ERROR: " . ($decoded['error']['message'] ?? $body) . "\n";
}
