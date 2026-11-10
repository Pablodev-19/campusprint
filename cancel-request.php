<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use the Cancel request button on your request history page.');
}

if (!validCsrfToken()) {
    http_response_code(403);
    exit('The request could not be verified. Return to your request history and try again.');
}

require_once __DIR__ . '/config/database.php';

$requestId = postText('request_id');

if (!preg_match('/^[1-9][0-9]*$/', $requestId)) {
    header('Location: my-request.php');
    exit;
}

$statement = $pdo->prepare(
    'SELECT id, status
     FROM print_requests
     WHERE id = :id
       AND student_id = :student_id
     LIMIT 1'
);

$statement->execute([
    'id' => (int) $requestId,
    'student_id' => $_SESSION['user_id'],
]);

$request = $statement->fetch();

if (!$request || $request['status'] !== 'pending') {
    header('Location: my-request.php');
    exit;
}

$updateStatement = $pdo->prepare(
    'UPDATE print_requests
     SET status = :status
     WHERE id = :id
       AND student_id = :student_id
       AND status = :current_status'
);

$updateStatement->execute([
    'status' => 'cancelled',
    'id' => (int) $requestId,
    'student_id' => $_SESSION['user_id'],
    'current_status' => 'pending',
]);

$historyStatement = $pdo->prepare(
    'INSERT INTO request_status_history
        (requesti_d, changed_by, status)
     VALUES
        (:requestid, :changedby, :status)'
);

$historyStatement->execute([
    'requestid' => (int) $requestId,
    'changedby' => $_SESSION['user_id'],
    'status' => 'cancelled',
]);

header('Location: my-request.php?cancelled=1');
exit;