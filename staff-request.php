<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

header('Cache-Control: no-store');

if (($_SESSION['user_role'] ?? '') !== 'staff') {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

$errors = [];
$success = '';

$allowedStatuses = [
    'pending',
    'accepted',
    'printing',
    'ready',
    'collected',
    'cancelled',
    'rejected',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validCsrfToken()) {
        $errors[] = 'The form could not be verified. Refresh and try again.';
    }

    $requestId = postText('request_id');
    $newStatus = postText('status');

    if (!preg_match('/^[1-9][0-9]*$/', $requestId)) {
        $errors[] = 'Invalid request selected.';
    }

    if (!in_array($newStatus, $allowedStatuses, true)) {
        $errors[] = 'Invalid status selected.';
    }

    if ($errors === []) {
        $checkStatement = $pdo->prepare(
            'SELECT id
             FROM print_requests
             WHERE id = :id
             LIMIT 1'
        );

        $checkStatement->execute(['id' => (int) $requestId]);
        $existingRequest = $checkStatement->fetch();

        if (!$existingRequest) {
            $errors[] = 'The selected request could not be found.';
        }
    }

   if ($errors === []) {
    $finalCost = postText('final_cost');

    if ($newStatus === 'ready') {
        if (
            $finalCost === '' ||
            !is_numeric($finalCost) ||
            (float) $finalCost <= 0
        ) {
            $errors[] = 'Enter a valid final cost before marking a request as ready.';
        }
    }

    if ($errors === []) {
        if ($newStatus === 'ready') {
            $updateStatement = $pdo->prepare(
                'UPDATE print_requests
                 SET status = :status,
                     final_cost = :final_cost
                 WHERE id = :id'
            );

            $updateStatement->execute([
                'status' => $newStatus,
                'final_cost' => number_format((float) $finalCost, 2, '.', ''),
                'id' => (int) $requestId,
            ]);
        } else {
            $updateStatement = $pdo->prepare(
                'UPDATE print_requests
                 SET status = :status
                 WHERE id = :id'
            );

            $updateStatement->execute([
                'status' => $newStatus,
                'id' => (int) $requestId,
            ]);
        }

        $historyStatement = $pdo->prepare(
            'INSERT INTO request_status_history
                (request_id, changed_by, status)
             VALUES
                (:requestid, :changedby, :status)'
        );

        $historyStatement->execute([
            'requestid' => (int) $requestId,
            'changedby' => $_SESSION['user_id'],
            'status' => $newStatus,
        ]);

        $success = 'Request #' . (int) $requestId . ' was updated to '
            . ucfirst($newStatus) . '.';
    }
}
}

$statement = $pdo->prepare(
    "SELECT
        pr.id,
        pr.page_count,
        pr.copies,
        pr.print_type,
        pr.color_mode,
        pr.instructions,
        pr.stored_filename,
        pr.status,
        pr.final_cost,
        pr.created_at,
        u.name AS student_name
     FROM print_requests pr
     INNER JOIN users u
         ON u.id = pr.student_id
     ORDER BY
        FIELD(pr.status, 'pending', 'accepted', 'printing', 'ready', 'collected', 'cancelled', 'rejected'),
        pr.created_at DESC"
);

$statement->execute();
$requests = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Request Management | CampusPrint</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a class="logo" href="index.php">
                Campus<span>Print</span>
            </a>

            <div class="header-actions">
                <a class="button button-secondary" href="dashboard.php">
                    Dashboard
                </a>

                <form action="logout.php" method="post">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escape($_SESSION['csrf_token']) ?>"
                    >

                    <button class="button button-secondary" type="submit">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="requests-page">
        <div class="container">
            <p class="eyebrow">STAFF REQUEST MANAGEMENT</p>

            <h1 class="page-heading">Printing requests</h1>

            <p class="page-description">
                Review student requests, open submitted PDFs, and update printing progress.
            </p>

            <?php if ($success !== ''): ?>
                <div class="alert alert-success" role="status">
                    <?= escape($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="alert alert-error" role="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($requests === []): ?>
                <div class="empty-state">
                    <h2>No requests yet</h2>

                    <p>
                        No student has submitted a printing request.
                    </p>
                </div>
            <?php else: ?>
                <div class="requests-table-wrapper">
                    <table class="requests-table staff-table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Student</th>
                                <th>Pages</th>
                                <th>Copies</th>
                                <th>Colour</th>
                                <th>Instructions</th>
                                <th>Status</th>
                                <th>Final cost</th>
                                <th>Submitted</th>
                                <th>Document</th>
                                <th>Update status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>#<?= (int) $request['id'] ?></td>

                                    <td><?= escape($request['student_name']) ?></td>

                                    <td><?= (int) $request['page_count'] ?></td>

                                    <td><?= (int) $request['copies'] ?></td>

                                    <td>
                                        <?= $request['color_mode'] === 'colour'
                                            ? 'Colour'
                                            : 'Black and white' ?>
                                    </td>

                                    <td class="instructions-cell">
                                        <?= escape($request['instructions'] ?: 'None') ?>
                                    </td>

                                    <td>
                                        <span class="status-badge status-<?= escape($request['status']) ?>">
                                            <?= escape(ucfirst($request['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                         <?= $request['final_cost'] !== null
                                         ? 'GH₵ ' . number_format((float) $request['final_cost'], 2)
                                            : 'Not set' ?>
                                     </td>

                                    <td>
                                        <?= escape(
                                            date(
                                                'd M Y, H:i',
                                                strtotime($request['created_at'])
                                            )
                                        ) ?>
                                    </td>

                                    <td>
                                        <a
                                            class="document-link"
                                           href="view-document.php?id=<?= (int) $request['id'] ?>"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            View PDF
                                        </a>
                                    </td>

                                    <td>
                                        <form
                                            class="status-form"
                                            action="staff-request.php"
                                            method="post"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= escape($_SESSION['csrf_token']) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="request_id"
                                                value="<?= (int) $request['id'] ?>"
                                            >
                                            <input
                                                  type="number"
                                                 name="final_cost"
                                                 min="0.01"
                                                 step="0.01"
                                                 placeholder="Final cost"
                                           >
                                           

                                            <select name="status" required>
                                                <?php foreach ($allowedStatuses as $status): ?>
                                                    <option
                                                        value="<?= escape($status) ?>"
                                                        <?= $request['status'] === $status ? 'selected' : '' ?>
                                                    >
                                                        <?= escape(ucfirst($status)) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <button class="button button-primary" type="submit">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>