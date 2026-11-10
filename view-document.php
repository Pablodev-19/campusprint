<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/config/database.php';

function documentNotFound(): void
{
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('The document could not be found or you do not have permission to view it.');
}

$requestId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($requestId === false || $requestId === null) {
    documentNotFound();
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$isStaff = ($_SESSION['user_role'] ?? '') === 'staff';

if ($isStaff) {
    $statement = $pdo->prepare(
        'SELECT id, stored_filename
         FROM print_requests
         WHERE id = :id
         LIMIT 1'
    );

    $statement->execute([
        'id' => $requestId,
    ]);
} else {
    $statement = $pdo->prepare(
        'SELECT id, stored_filename
         FROM print_requests
         WHERE id = :id
           AND student_id = :student_id
         LIMIT 1'
    );

    $statement->execute([
        'id' => $requestId,
        'student_id' => $userId,
    ]);
}

$request = $statement->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    documentNotFound();
}

$storedFilename = (string) $request['stored_filename'];

// Match the generated filenames used by submit-request.php.
if (!preg_match('/\A[a-f0-9]{32}\.pdf\z/', $storedFilename)) {
    documentNotFound();
}

$filePath = __DIR__ . '/uploads/requests/' . $storedFilename;

if (!is_file($filePath) || !is_readable($filePath)) {
    documentNotFound();
}

$fileHandle = fopen($filePath, 'rb');

if ($fileHandle === false) {
    documentNotFound();
}

// Release the session lock before sending the document.
session_write_close();

header('Content-Type: application/pdf');
header(
    'Content-Disposition: inline; filename="request-'
    . (int) $request['id']
    . '.pdf"'
);

fpassthru($fileHandle);
fclose($fileHandle);
exit;