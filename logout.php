<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use the Log out button on your dashboard.');
}

if (!validCsrfToken()) {
    http_response_code(403);
    exit('The request could not be verified. Return to your dashboard and try again.');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $parameters['path'],
        'domain' => $parameters['domain'],
        'secure' => $parameters['secure'],
        'httponly' => $parameters['httponly'],
        'samesite' => $parameters['samesite'] ?? 'Lax',
    ]);
}

session_destroy();

header('Location: login.php');
exit;