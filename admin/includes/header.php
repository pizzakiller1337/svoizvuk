<?php
require_once __DIR__ . '/icons.php';
// Считаем количество "новых" заказов для бейджа в сайдбаре.
// Если таблицы нет или у неё старая схема — просто не показываем бейдж.
$new_orders_badge = 0;
if (isset($link) && $link) {
    try {
        $res = @mysqli_query($link, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'new'");
        if ($res) {
            $new_orders_badge = (int)mysqli_fetch_assoc($res)['cnt'];
        }
    } catch (Throwable $e) {
        // Игнорируем — бейдж просто не отображается
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title ?? 'Админ панель') ?> — Свой звук</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/assets/logo/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/logo/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/logo/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#171717">
    <link rel="stylesheet" href="/admin/includes/admin.css?v=4">
</head>
<body>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Свой звук</h2>
            <p>Панель администратора</p>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Главное</div>
                <a href="/admin/index.php"
                   class="nav-item <?= ($current_page ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <span class="icon"><?= admin_icon('gauge') ?></span> Дашборд
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Продажи</div>
                <a href="/admin/orders/index.php"
                   class="nav-item <?= ($current_page ?? '') === 'orders' ? 'active' : '' ?>">
                    <span class="icon"><?= admin_icon('package') ?></span> Заказы
                    <?php if ($new_orders_badge > 0): ?>
                        <span class="nav-badge"><?= $new_orders_badge ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Каталог</div>
                <a href="/admin/products/index.php"
                   class="nav-item <?= ($current_page ?? '') === 'products' ? 'active' : '' ?>">
                    <span class="icon"><?= admin_icon('vinyl-record') ?></span> Пластинки
                </a>
                <a href="/admin/products/add.php"
                   class="nav-item <?= ($current_page ?? '') === 'add_product' ? 'active' : '' ?>">
                    <span class="icon"><?= admin_icon('plus-circle') ?></span> Добавить пластинку
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Сайт</div>
                <a href="/" class="nav-item" target="_blank">
                    <span class="icon"><?= admin_icon('arrow-square-out') ?></span> Открыть сайт
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <a href="/admin/logout.php">Выйти (<?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?>)</a>
        </div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title"><?= htmlspecialchars($page_title ?? 'Панель управления') ?></div>
            <div class="topbar-user">Привет, <?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?></div>
        </div>
        <div class="content">
