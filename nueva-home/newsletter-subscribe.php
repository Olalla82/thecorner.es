<?php
declare(strict_types=1);

const NEWSLETTER_HOME = '/?newsletter=';
const NEWSLETTER_SUCCESS = '/gracias-suscripcion/?estado=pendiente';

function redirect_home(string $status): never
{
    header('Location: ' . NEWSLETTER_HOME . rawurlencode($status) . '#newsletter', true, 303);
    exit;
}

function redirect_success(): never
{
    header('Location: ' . NEWSLETTER_SUCCESS, true, 303);
    exit;
}

header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

if (trim((string) ($_POST['website'] ?? '')) !== '') {
    redirect_success();
}

$email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$consent = (string) ($_POST['newsletter_consent'] ?? '');

if ($email === false || $consent !== '1') {
    redirect_home('invalid');
}

$configFile = __DIR__ . '/newsletter-config.php';
if (!is_file($configFile)) {
    redirect_home('config');
}

$config = require $configFile;
$apiKey = trim((string) (getenv('BREVO_API_KEY') ?: ($config['api_key'] ?? '')));

if ($apiKey === '' || $apiKey === 'PEGA_AQUI_LA_API_KEY_DE_BREVO') {
    redirect_home('config');
}

$payload = json_encode([
    'email' => $email,
    'includeListIds' => [(int) ($config['list_id'] ?? 56)],
    'templateId' => (int) ($config['doi_template_id'] ?? 129),
    'redirectionUrl' => (string) ($config['doi_redirect_url'] ?? 'https://thecorner.es/gracias-suscripcion/?estado=confirmada'),
], JSON_UNESCAPED_SLASHES);

if ($payload === false || !function_exists('curl_init')) {
    redirect_home('error');
}

$request = curl_init('https://api.brevo.com/v3/contacts/doubleOptinConfirmation');
curl_setopt_array($request, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 12,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json',
        'api-key: ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS => $payload,
]);

$response = curl_exec($request);
$statusCode = (int) curl_getinfo($request, CURLINFO_RESPONSE_CODE);
$curlError = curl_error($request);
curl_close($request);

if ($response !== false && $curlError === '' && $statusCode >= 200 && $statusCode < 300) {
    redirect_success();
}

error_log('Brevo newsletter subscription failed with HTTP status ' . $statusCode);
redirect_home('error');
