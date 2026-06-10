<?php
$cart_count = array_sum($_SESSION['cart'] ?? []);
$is_logged_in = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
// h1 — только на главной; на внутренних страницах свой h1 (page-title),
// двух h1 на странице быть не должно
$is_home_page = basename($_SERVER['SCRIPT_NAME']) === 'index.php';
$site_title_tag = $is_home_page ? 'h1' : 'p';
?>

<div class="top-bar">
    <div class="container">
        <div class="top-info">
            <span class="info-item">Доставка по России — СДЭК и Почта</span>
            <span class="info-item">Пн–Пт 11–20, Сб 12–18</span>
        </div>
        <div class="top-actions">
            <a href="about.php" class="top-link">О магазине</a>
            <a href="cart.php" class="cart-btn">
                Корзина
                <span class="cart-count"><?= $cart_count ?></span>
            </a>
            <?php if ($is_logged_in): ?>
                <span class="top-link" style="opacity:0.6;"><?= htmlspecialchars($username) ?></span>
                <a href="logout.php" class="login-btn">Выйти</a>
            <?php else: ?>
                <a href="login.php" class="login-btn">Войти</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="main-header">
    <div class="header-bg" aria-hidden="true">
        <div class="header-grain"></div>
        <div class="header-grooves"></div>
    </div>

    <div class="container header-inner">
        <a href="index.php" class="logo-link">
            <img src="assets/logo/logo.png?v=2" alt="Свой звук" class="logo-icon">
            <<?= $site_title_tag ?> class="site-title">Свой звук</<?= $site_title_tag ?>>
        </a>
    </div>
</header>