<?php
// ============================================================
// api/chat.php — Receives the user's message from app.js,
// forwards it to the Gemini API, and returns the reply as JSON.
//
// This file did not exist before — it is the missing piece that
// caused "حدث خطأ أثناء الاتصال بالخادم" in the browser, since
// every fetch() to it was hitting a 404.
// ============================================================

header('Content-Type: application/json; charset=utf-8');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

// Load API key + model name from config.php (one level up)
require_once __DIR__ . '/../config.php';

// Read and decode the JSON body sent by app.js: { "prompt": "..." }
$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody, true);

$prompt = isset($input['prompt']) ? trim((string) $input['prompt']) : '';

if ($prompt === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing "prompt" field in request body.']);
    exit;
}

if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '' || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
    http_response_code(500);
    echo json_encode(['error' => 'Server is not configured: add a real GEMINI_API_KEY in config.php.']);
    exit;
}

$model = defined('GEMINI_MODEL') && GEMINI_MODEL !== '' ? GEMINI_MODEL : 'gemini-1.5-flash';
$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . GEMINI_API_KEY;

$payload = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => $prompt],
            ],
        ],
    ],
];

// Use cURL if available (works on almost all hosts, including InfinityFree)
if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErrNo = curl_errno($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlErrNo !== 0 || $response === false) {
        http_response_code(502);
        echo json_encode(['error' => 'Could not reach Gemini API.', 'details' => $curlError]);
        exit;
    }
} else {
    // Fallback for hosts without cURL enabled
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($payload),
            'timeout' => 30,
            'ignore_errors' => true,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);
    $httpCode = 200;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $httpCode = (int) $m[1];
    }

    if ($response === false) {
        http_response_code(502);
        echo json_encode(['error' => 'Could not reach Gemini API (file_get_contents fallback failed).']);
        exit;
    }
}

$data = json_decode($response, true);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    $apiMessage = $data['error']['message'] ?? 'Unknown error from Gemini API.';
    echo json_encode(['error' => 'Gemini API error: ' . $apiMessage]);
    exit;
}

$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

if ($reply === null) {
    http_response_code(502);
    echo json_encode(['error' => 'Unexpected response format from Gemini API.', 'raw' => $data]);
    exit;
}

echo json_encode(['reply' => $reply]);
