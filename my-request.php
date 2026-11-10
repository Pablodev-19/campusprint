<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

header('Cache-Control: no-store');

require_once __DIR__ . '/config/database.php';

$statement = $pdo->prepare(
    'SELECT
        id,
        page_count,
        copies,
        sides,
        estimated_cost,
        print_type,
        color_mode,
        instructions,
        stored_filename,
        status,
        final_cost,
        created_at
     FROM print_requests
     WHERE student_id = :student_id
     ORDER BY created_at DESC'
);

$statement->execute([
    'student_id' => $_SESSION['user_id'],
]);

$requests = $statement->fetchAll();
$cancelled = isset($_GET['cancelled']) && $_GET['cancelled'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Requests | CampusPrint</title>

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

                <a class="button button-primary" href="submit-request.php">
                    New request
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
            <p class="eyebrow">REQUEST HISTORY</p>

            <h1 class="page-heading">My printing requests</h1>

            <p class="page-description">
                Track the documents you have submitted to the CampusPrint shop.
            </p>
             <?php if ($cancelled): ?>
             <div class="alert alert-success" role="status">
                Your request was cancelled successfully.
             </div>
               <?php endif; ?>
            <?php if ($requests === []): ?>
                <div class="empty-state">
                    <h2>No requests yet</h2>

                    <p>
                        You have not submitted any printing requests.
                        Upload a PDF to create your first request.
                    </p>

                    <a class="button button-primary" href="submit-request.php">
                        Submit a request
                    </a>
                </div>
            <?php else: ?>
                <div class="requests-table-wrapper">
                    <table class="requests-table">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Print type</th>
                                <th>Pages</th>
                                <th>Copies</th>
                                <th>Printing sides</th>
                                <th>Colour</th>
                                <th>Status</th>
                                <th>Estimated cost</th>
                                <th>Final cost</th>
                                <th>Submitted</th>
                                <th>Document</th>
                                <th>Action</th>
                                <th>Instructions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>#<?= (int) $request['id'] ?></td>

                                    <td><?= escape(ucfirst($request['print_type'] ?? 'Document')) ?></td>

                                    <td><?= (int) $request['page_count'] ?></td>

                                    <td><?= (int) $request['copies'] ?></td>
                                    <td>
                                       <?= $request['sides'] === 'double'
                                        ? 'Double-sided'
                                         : 'Single-sided' ?>
                                     </td> 

                                    <td>
                                       <?= $request['color_mode'] === 'colour' ? 'Colour' : 'Black and white' ?>
                                    </td>

                                    <td>
                                        <span class="status-badge status-<?= escape($request['status']) ?>">
                                            <?= escape(ucfirst($request['status'])) ?>
                                        </span>
                                    </td>
                                     <td>
                                           <?= $request['estimated_cost'] !== null
                                           ? 'GH₵ ' . number_format((float) $request['estimated_cost'], 2)
                                             : 'Not available' ?>
                                    </td>

                                    <td>
                                       <?= $request['final_cost'] !== null
                                        ? 'GH₵ ' . number_format((float) $request['final_cost'], 2)
                                        : 'Awaiting staff confirmation' ?>
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
                                          <?php if ($request['status'] === 'pending'): ?>
                                           <form
                                             class="cancel-form"
                                             action="cancel-request.php"
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

                                                        <button class="button button-danger" type="submit">
                                                         Cancel
                                                         </button>
                                                        </form>
                                                    <?php else: ?>
                                                       <span class="muted-text">—</span>
                                                       <?php endif; ?>
                                        </td>
                                    <td>
                                        <?= escape($request['instructions'] ?? 'None') ?>
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