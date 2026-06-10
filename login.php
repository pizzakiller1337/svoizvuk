<?php
session_start();

require 'db.php';
$link = get_db();

$error_message   = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = 'Все поля обязательны для заполнения';
    } else {
        $stmt = mysqli_prepare($link, "SELECT * FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        $auth_ok = false;
        if ($user) {
            $stored = $user['password'];
            $info   = password_get_info($stored);
            if (!empty($info['algo'])) {
                // Современный хеш — проверяем через password_verify
                $auth_ok = password_verify($password, $stored);
            } elseif (hash_equals($stored, $password)) {
                // Legacy: пароль хранился в открытом виде. Принимаем
                // и сразу пересохраняем как хеш — постепенная миграция.
                $auth_ok = true;
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $upd = mysqli_prepare($link, "UPDATE users SET password = ? WHERE user_id = ?");
                mysqli_stmt_bind_param($upd, "si", $new_hash, $user['user_id']);
                mysqli_stmt_execute($upd);
            }
        }

        if ($auth_ok) {
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];

            if ($user['role'] === 'admin') {
                $_SESSION['admin_id'] = $user['user_id'];
                $_SESSION['role']     = 'admin';
                header('Location: /admin/index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error_message = 'Неверный email или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход — Свой звук</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/assets/logo/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/logo/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/logo/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#171717">
    <link rel="stylesheet" href="styles.css?v=6">
</head>
<body>
<?php require 'header.php'; ?>
<main>
    <section class="auth-section">
        <div class="auth-form-side">
            <h1 class="auth-title">Вход</h1>

            <?php if (!empty($error_message)): ?>
                <div class="message error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="message success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="auth-form">
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="btn-login">Войти</button>
            </form>

            <p class="auth-switch">
                Ещё не зарегистрированы? <a href="register.php">Создать аккаунт</a>
            </p>
        </div>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>