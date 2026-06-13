<?php
require_once __DIR__ . '/icons.php';
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
                <?= site_icon('cart', 17) ?>
                Корзина
                <span class="cart-count"><?= $cart_count ?></span>
            </a>
            <?php if ($is_logged_in): ?>
                <details class="user-menu">
                    <summary class="user-menu-trigger">
                        <?= site_icon('user', 15) ?>
                        <span class="user-menu-name"><?= htmlspecialchars($username) ?></span>
                        <svg class="user-menu-chev" width="11" height="11" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"></path></svg>
                    </summary>
                    <div class="user-menu-dropdown">
                        <div class="user-menu-head">Вы вошли как<br><strong><?= htmlspecialchars($username) ?></strong></div>
                        <a href="orders.php" class="user-menu-item">Мои заказы</a>
                        <a href="cart.php" class="user-menu-item">Корзина<span class="user-menu-badge"><?= $cart_count ?></span></a>
                        <a href="about.php" class="user-menu-item">О магазине</a>
                        <div class="user-menu-sep"></div>
                        <a href="logout.php" class="user-menu-item user-menu-item--danger">Выйти</a>
                    </div>
                </details>
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