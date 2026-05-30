<?php
// ── Configuração ──────────────────────────────────────────────────────────────
define('FASTDEPIX_TOKEN', 'fdpx_sua_chave_api_aqui'); // << coloque sua chave aqui
define('FASTDEPIX_BASE',  'https://fastdepix.space/api/v1');

// ── CORS: permite apenas este mesmo domínio ───────────────────────────────────
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Monta a URL de destino ────────────────────────────────────────────────────
$path  = $_GET['path'] ?? '';
$path  = '/' . ltrim($path, '/');
$query = $_SERVER['QUERY_STRING'] ?? '';
$query = preg_replace('/(^|&)path=[^&]*/', '', $query); // remove param "path"
$query = ltrim($query, '&');

$url = FASTDEPIX_BASE . $path;
if ($query) $url .= '?' . $query;

// ── Corpo da requisição ───────────────────────────────────────────────────────
$body = file_get_contents('php://input');

// ── cURL ──────────────────────────────────────────────────────────────────────
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => $_SERVER['REQUEST_METHOD'],
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . FASTDEPIX_TOKEN,
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS     => $body ?: null,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'Proxy error: ' . $error]);
    exit;
}

http_response_code($httpCode);
echo $response;
