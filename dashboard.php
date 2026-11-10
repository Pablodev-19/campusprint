<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

header('Cache-Control: no-store');

require_once __DIR__ . '/config/database.php';

$statement = $pdo->prepare(
    'SELECT id, name, email, role
     FROM users
     WHERE id = :id'
);

$statement->execute(['id' => $_SESSION['user_id']]);
$currentUser = $statement->fetch();

if (
    !$currentUser ||
    !in_array($currentUser['role'], ['student', 'staff'], true)
) {
    $_SESSION = [];
    session_destroy();

    header('Location: login.php');
    exit;
}

$_SESSION['user_name'] = $currentUser['name'];
$_SESSION['user_role'] = $currentUser['role'];

$isStaff = $currentUser['role'] === 'staff';

$countSql = "
    SELECT
        COUNT(*) AS total_requests,
        COALESCE(SUM(status = 'pending'), 0) AS pending_requests,
        COALESCE(SUM(status = 'ready'), 0) AS ready_requests
    FROM print_requests
";

if ($isStaff) {
    $countsStatement = $pdo->query($countSql);
} else {
    $countsStatement = $pdo->prepare(
        $countSql . ' WHERE student_id = :student_id'
    );

    $countsStatement->execute([
        'student_id' => $currentUser['id'],
    ]);
}

$counts = $countsStatement->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | CampusPrint</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a class="logo" href="index.php">
                Campus<span>Print</span>
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
    </header>

    <main class="dashboard-page">
        <div class="container">
            <p class="eyebrow">
                <?= $isStaff ? 'STAFF DASHBOARD' : 'STUDENT DASHBOARD' ?>
            </p>

            <h1 class="dashboard-heading">
                Welcome, <?= escape($currentUser['name']) ?>
            </h1>

            <p class="dashboard-description">
                <?= $isStaff
                    ? 'View an overview of the printing shop’s requests.'
                    : 'View an overview of your printing requests.' ?>
            </p>

            <div class="stats-grid">
                <article class="stat-card">
                    <h2>Total requests</h2>
                    <p><?= (int) $counts['total_requests'] ?></p>
                </article>

                <article class="stat-card">
                    <h2>Pending review</h2>
                    <p><?= (int) $counts['pending_requests'] ?></p>
                </article>

                <article class="stat-card">
                    <h2>Ready for collection</h2>
                    <p><?= (int) $counts['ready_requests'] ?></p>
                </article>
            </div>

         <section class="development-note">
    <h2>
        <?= $isStaff
            ? 'Request management is coming next'
            : 'Submit a new printing request' ?>
    </h2>

    <p>
        <?= $isStaff
            ? 'The request queue and processing controls will be added in a later build step.'
            : 'Upload a PDF, choose your printing options, and send your request to the shop.' ?>
    </p>

   <?php if ($isStaff): ?>
    <div class="button-group">
        <a class="button button-primary" href="staff-request.php">
            Manage requests
        </a>
    </div>
<?php else: ?>
    <div class="button-group">
        <a class="button button-primary" href="submit-request.php">
            Submit a request
        </a>

        <a class="button button-secondary" href="my-request.php">
            View my requests
        </a>
    </div>
<?php endif; ?>
</section>
        </div>
    </main>
</body>
</html>