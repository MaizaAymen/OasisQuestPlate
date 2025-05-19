<?php

// public/chat.php

$api_key = getenv('OPENROUTER_API_KEY') ?: '<YOUR_OPENROUTER_API_KEY>';
$model   = 'google/gemini-2.0-flash-exp:free';

// Prepare your site info (optional)
$site_url  = 'http://127.0.0.1:8000';
$site_name = 'Nefta Dates';

$url = 'https://api.openrouter.ai/v1/chat/completions';

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key,
    'Referer: ' . $site_url,
    'X-Title: ' . $site_name,
];

// Build a conversation with a text message and an image
$data = [
    'model' => $model,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                ['type' => 'text',      'text' => 'What is shown in this image?'],
                ['type' => 'image_url', 'image_url' => ['url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/dd/Gfp-wisconsin-madison-the-nature-boardwalk.jpg/2560px-Gfp-wisconsin-madison-the-nature-boardwalk.jpg']]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
    exit(1);
}
curl_close($ch);

$result = json_decode($response, true);

echo "<h1>Chatbot Response</h1>\n";
echo '<pre>' . htmlspecialchars(print_r($result['choices'][0]['message']['content'] ?? 'No response', true)) . '</pre>';
