<?php
session_start();

// Если уже авторизован — на дашборд
if (isset($_SESSION['admin_id']) && $_SESSION['role'] === 'admin') {
    header('Location: /admin/index.php');
    exit;
}

require_once 'includes/auth.php';
$link = get_db();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($link, "SELECT * FROM users WHERE email = ? AND role = 'admin'");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin  = mysqli_fetch_assoc($result);

    $auth_ok = false;
    if ($admin) {
        $stored = $admin['password'];
        $info   = password_get_info($stored);
        if (!empty($info['algo'])) {
            $auth_ok = password_verify($password, $stored);
        } elseif (hash_equals($stored, $password)) {
            // Legacy: пароль был в открытом виде. Принимаем и мигрируем на хеш.
            $auth_ok = true;
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $upd = mysqli_prepare($link, "UPDATE users SET password = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($upd, "si", $new_hash, $admin['user_id']);
            mysqli_stmt_execute($upd);
        }
    }

    if ($auth_ok) {
        $_SESSION['admin_id']   = $admin['user_id'];
        $_SESSION['admin_name'] = $admin['username'];
        $_SESSION['role']       = 'admin';
        header('Location: /admin/index.php');
        exit;
    } else {
        $error = 'Неверный email или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход — Админ панель</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/assets/logo/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/logo/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/logo/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#171717">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: system-ui, sans-serif; }
        body { background: #111; color: #e5e5e5; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { background: #1e1e1e; border: 1px solid #2a2a2a; border-radius: 12px; padding: 48px 40px; width: 100%; max-width: 420px; }
        .login-box h1 { font-size: 1.5rem; margin-bottom: 8px; color: #fff; }
        .login-box p { color: #888; font-size: 0.9rem; margin-bottom: 32px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; font-size: 0.85rem; color: #aaa; }
        input { width: 100%; padding: 12px 14px; background: #2a2a2a; border: 1px solid #333; border-radius: 8px; color: #e5e5e5; font-size: 0.95rem; outline: none; transition: border-color 0.2s; }
        input:focus { border-color: #555; }
        .btn { width: 100%; padding: 13px; background: #fff; color: #111; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background: #e0e0e0; }
        .error { background: #3a1a1a; color: #f87171; border: 1px solid #5a2a2a; border-radius: 8px; padding: 12px 14px; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Свой звук</h1>
        <p>Панель администратора</p>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Войти</button>
        </form>
    </div>
</body>
</html>