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
    <link rel="stylesheet" href="/tokens.css?v=3">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-sans); }
        body { background: var(--surface-0); color: var(--text-primary); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        :where(a, button, input):focus-visible { outline: 2px solid rgba(255, 255, 255, 0.75); outline-offset: 2px; }
        .login-box { background: var(--surface-2); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 48px 40px; width: 100%; max-width: 420px; }
        .login-box h1 { font-size: 1.5rem; margin-bottom: 8px; color: var(--text-white); }
        .login-box p { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 32px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 6px; font-size: 0.85rem; color: var(--text-secondary); }
        input { width: 100%; padding: 12px 14px; background: var(--surface-3); border: 1px solid var(--border-medium); border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.95rem; transition: border-color var(--motion-fast); }
        input:focus { border-color: var(--border-strong); }
        .btn { width: 100%; padding: 13px; background: var(--accent); color: var(--accent-inverse); border: none; border-radius: var(--radius-md); font-size: 1rem; font-weight: 600; cursor: pointer; transition: background var(--motion-fast); }
        .btn:hover { background: var(--accent-hover); }
        .error { background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger-border); border-radius: var(--radius-md); padding: 12px 14px; margin-bottom: 20px; font-size: 0.9rem; }
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