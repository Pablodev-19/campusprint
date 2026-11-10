<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$name = '';
$email = '';

$success = $_SESSION['registration_success'] ?? '';
unset($_SESSION['registration_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(postText('name'));
    $email = strtolower(trim(postText('email')));
    $password = postText('password');
    $confirmPassword = postText('confirm_password');
    $submittedToken = postText('csrf_token');

    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $errors[] = 'The form could not be verified. Refresh and try again.';
    }

    if ($name === '' || mb_strlen($name, 'UTF-8') > 100) {
        $errors[] = 'Enter your name using no more than 100 characters.';
    }

    if (
        strlen($email) > 150 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $errors[] = 'Enter a valid email address of no more than 150 characters.';
    }

    if (
        mb_strlen($password, 'UTF-8') < 15 ||
        strlen($password) > 72
    ) {
        $errors[] = 'Use at least 15 characters and no more than 72 bytes for your password.';
    }

    if (str_contains($password, "\0")) {
        $errors[] = 'Your password contains an unsupported character.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'The passwords do not match.';
    }

    if ($errors === []) {
        try {
            $passwordHash = password_hash(
                $password,
                PASSWORD_BCRYPT
            );

            $statement = $pdo->prepare(
                "INSERT INTO users
                    (name, email, password_hash, role)
                 VALUES
                    (:name, :email, :password_hash, 'student')"
            );

            $statement->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => $passwordHash,
            ]);

            $_SESSION['registration_success'] =
                'Your student account has been created successfully.';

            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header('Location: register.php');
            exit;
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                $errors[] = 'An account already uses this email address.';
            } else {
                error_log(
                    'CampusPrint registration failed: '
                    . $exception->getMessage()
                );

                $errors[] = 'Registration could not be completed. Try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | CampusPrint</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a class="logo" href="index.php">
                Campus<span>Print</span>
            </a>

            <a href="index.php">Back to home</a>
        </div>
    </header>

    <main class="auth-page">
        <section class="auth-card" aria-labelledby="registration-heading">
            <p class="eyebrow">STUDENT REGISTRATION</p>

            <h1 id="registration-heading">Create your account</h1>

            <p class="auth-description">
                Register to prepare for submitting and tracking
                your printing requests.
            </p>

            <?php if ($success !== ''): ?>
                <div class="alert alert-success" role="status">
                    <?= escape($success) ?>
                   <a href="login.php">Log in to continue.</a>
                </div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="alert alert-error" role="alert">
                    <p>Please correct the following:</p>

                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="register.php" method="post">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escape($_SESSION['csrf_token']) ?>"
                >

                <div class="form-field">
                    <label for="name">Full name</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        maxlength="100"
                        autocomplete="name"
                        value="<?= escape($name) ?>"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="email">Email address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="150"
                        autocomplete="email"
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
                        minlength="15"
                        autocomplete="new-password"
                        aria-describedby="password-help"
                        required
                    >

                    <small id="password-help" class="field-help">
                        Use a long passphrase of at least 15 characters.
                        The server limit is 72 bytes.
                        Do not reuse an important personal password.
                    </small>
                </div>

                <div class="form-field">
                    <label for="confirm-password">Confirm password</label>

                    <input
                        type="password"
                        id="confirm-password"
                        name="confirm_password"
                        minlength="15"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button class="button button-primary" type="submit">
                    Create student account
                </button>
            </form>
        </section>
    </main>
</body>
</html>