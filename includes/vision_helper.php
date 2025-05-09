<?php
/**
 * Google Vision API - SafeSearch Detection using API Key (no SDK)
 * ✅ Requires a JSON file: includes/vision_api_key.json
 */

function isImageSafe($imagePath) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);

    $log = function ($message, $file = 'vision.log') use ($logDir) {
        file_put_contents("$logDir/$file", "[" . date('Y-m-d H:i:s') . "] $message\n", FILE_APPEND);
    };

    $log("isImageSafe() called for: $imagePath", 'vision_usage.log');

    $keyFile = __DIR__ . '/vision_api_key.json';
    if (!file_exists($keyFile)) {
        $log("❌ API key file not found: $keyFile", 'vision_error.log');
        return false;
    }

    $data = json_decode(file_get_contents($keyFile), true);
    $apiKey = $data['api_key'] ?? null;
    if (!$apiKey) {
        $log("❌ Missing 'api_key' in vision_api_key.json", 'vision_error.log');
        return false;
    }

    $imageContent = base64_encode(file_get_contents($imagePath));
    $payload = json_encode([
        'requests' => [[
            'image' => ['content' => $imageContent],
            'features' => [['type' => 'SAFE_SEARCH_DETECTION']]
        ]]
    ]);

    $url = "https://vision.googleapis.com/v1/images:annotate?key=" . urlencode($apiKey);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_SSL_VERIFYPEER => false, // Only for local dev
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) {
        $log("❌ cURL error: " . curl_error($ch), 'vision_error.log');
        curl_close($ch);
        return false;
    }
    curl_close($ch);

    $log("Raw response: $response", 'vision_raw.log');

    if ($httpCode !== 200) {
        $log("❌ HTTP Error: $httpCode", 'vision_error.log');
        return false;
    }

    $result = json_decode($response, true);
    $safe = $result['responses'][0]['safeSearchAnnotation'] ?? null;

    if (!$safe) {
        $log("❌ No safeSearchAnnotation found in response", 'vision_error.log');
        return false;
    }

    $log("SafeSearch result: " . json_encode($safe), 'vision_safesearch.log');

    // Check if content is unsafe
    $flags = ['LIKELY', 'VERY_LIKELY'];
    if (
        in_array($safe['adult'] ?? '', $flags) ||
        in_array($safe['violence'] ?? '', $flags) ||
        in_array($safe['racy'] ?? '', $flags)
    ) {
        $log("⚠️ Image flagged — adult: {$safe['adult']}, violence: {$safe['violence']}, racy: {$safe['racy']}", 'vision_flagged.log');
        return false;
    }

    return true;
}
