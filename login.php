<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim(postText('email')));
    $password = postText('password');

    if (!validCsrfToken()) {
        $errors[] = 'The form could not be verified. Refresh and try again.';
    }

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150 ||
        $password === '' ||
        strlen($password) > 72 ||
        str_contains($password, "\0")
    ) {
        $errors[] = 'Enter a valid email address and password.';
    }

    if ($errors === []) {
        $statement = $pdo->prepare(
            'SELECT id, name, email, password_hash, role
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if (
            $user &&
            password_verify($password, $user['password_hash']) &&
            in_array($user['role'], ['student', 'staff'], true)
        ) {
            session_regenerate_id(true);

            $_SESSION = [
                'user_id' => (int) $user['id'],
                'user_name' => $user['name'],
                'user_role' => $user['role'],
                'csrf_token' => bin2hex(random_bytes(32)),
            ];

            header('Location: dashboard.php');
            exit;
        }

        $errors[] = 'Invalid email address or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Log In | CampusPrint</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a class="logo" href="index.php">
                Campus<span>Print</span>
            </a>

            <a href="register.php">Create account</a>
        </div>
    </header>

    <main class="auth-page">
        <section class="auth-card" aria-labelledby="login-heading">
            <p class="eyebrow">WELCOME BACK</p>

            <h1 id="login-heading">Log in to CampusPrint</h1>

            <p class="auth-description">
                Enter the email address and password you registered with.
            </p>

            <?php if ($errors !== []): ?>
                <div class="alert alert-error" role="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escape($_SESSION['csrf_token']) ?>"
                >

                <div class="form-field">
                    <label for="email">Email address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="150"
                        autocomplete="username"
                        value="<?= escape($email) ?>"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button class="button button-primary" type="submit">
                    Log in
                </button>
            </form>

            <p class="auth-bottom-link">
                No account yet?
                <a href="register.php">Create a student account</a>
            </p>
        </section>
    </main>
</body>
</html>