<?php
session_start();

require 'db.php';
$link = get_db();

$error_message   = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $phone    = trim($_POST['phone']);

    if (empty($username) || empty($email) || empty($password) || empty($phone)) {
        $error_message = 'Пожалуйста, заполните все поля.';
    } else {
        $check = mysqli_prepare($link, "SELECT user_id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error_message = 'Пользователь с таким email уже существует.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($link,
                "INSERT INTO users (username, email, phone, password, created_at) VALUES (?, ?, ?, ?, NOW())"
            );
            mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $phone, $password_hash);
            if (mysqli_stmt_execute($stmt)) {
                // Автологин: сразу заводим сессию и отправляем в каталог.
                $new_user_id           = mysqli_insert_id($link);
                $_SESSION['user_id']   = $new_user_id;
                $_SESSION['username']  = $username;
                $_SESSION['email']     = $email;
                header('Location: index.php');
                exit;
            } else {
                $error_message = 'Ошибка регистрации. Попробуйте позже.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация — Свой звук</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/assets/logo/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/logo/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/logo/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#171717">
    <link rel="stylesheet" href="styles.css?v=8">
</head>
<body>
<?php require 'header.php'; ?>
<main>
    <section class="auth-section">
        <div class="auth-form-side">
            <h1 class="page-title">Создать аккаунт</h1>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="auth-form">
                <div class="form-group">
                    <label class="form-label" for="username">Имя</label>
                    <input type="text" id="username" name="username" class="form-input" required
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="auth-form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Пароль</label>
                        <input type="password" id="password" name="password" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Телефон</label>
                        <input type="text" id="phone" name="phone" class="form-input" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-login">Зарегистрироваться</button>
            </form>

            <p class="auth-switch">
                Уже есть аккаунт? <a href="login.php">Войти</a>
            </p>
        </div>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>