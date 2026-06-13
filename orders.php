<?php
session_start();
require __DIR__ . '/db.php';
require_once __DIR__ . '/img_helpers.php';
$link = get_db();

// Только для авторизованных
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$uid = (int) $_SESSION['user_id'];

$status_labels = [
    'new'        => 'Новый',
    'processing' => 'В обработке',
    'shipped'    => 'Отправлен',
    'delivered'  => 'Доставлен',
    'cancelled'  => 'Отменён',
];
$delivery_labels = ['courier' => 'Курьером', 'post' => 'Почта России', 'pickup' => 'Самовывоз'];
$payment_labels  = ['card' => 'Картой', 'sbp' => 'СБП', 'cash' => 'При получении'];

// Заказы пользователя
$stmt = mysqli_prepare($link, "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$orders = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Позиции всех заказов одним запросом
$items_by_order = [];
if ($orders) {
    $ids = implode(',', array_map(fn($o) => (int) $o['order_id'], $orders));
    $res = mysqli_query($link, "SELECT * FROM order_items WHERE order_id IN ($ids) ORDER BY item_id ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $items_by_order[$row['order_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заказы — Свой звук</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/assets/logo/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/logo/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/logo/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#171717">
    <?php require __DIR__ . "/meta_og.php"; ?>
    <link rel="stylesheet" href="styles.css?v=15">
</head>
<body>
<?php require 'header.php'; ?>

<main class="orders-page">
    <div class="container">
        <h1 class="page-title">Мои заказы</h1>

        <?php if (!$orders): ?>
            <div class="orders-empty">
                <p>Здесь появятся ваши заказы. Пока ни одного — самое время это исправить.</p>
                <a href="index.php" class="btn btn-primary">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $o): ?>
                    <?php $items = $items_by_order[$o['order_id']] ?? []; ?>
                    <article class="order-card">
                        <div class="order-card-header">
                            <div>
                                <div class="order-card-label">Заказ</div>
                                <div class="order-number"><?= htmlspecialchars($o['order_number']) ?></div>
                            </div>
                            <div class="order-head-right">
                                <span class="order-status order-status-<?= htmlspecialchars($o['status']) ?>">
                                    <?= htmlspecialchars($status_labels[$o['status']] ?? $o['status']) ?>
                                </span>
                                <div class="order-card-date"><?= date('d.m.Y', strtotime($o['created_at'])) ?></div>
                            </div>
                        </div>

                        <div class="order-items">
                            <?php foreach ($items as $it): ?>
                                <div class="summary-item">
                                    <picture>
                                        <?= webp_source($it['image_url'] ?? '') ?>
                                        <img src="<?= htmlspecialchars($it['image_url'] ?? '') ?>"
                                             alt="<?= htmlspecialchars($it['title']) ?>" loading="lazy" decoding="async">
                                    </picture>
                                    <div class="summary-item-info">
                                        <div class="summary-item-title"><?= htmlspecialchars($it['title']) ?></div>
                                        <div class="summary-item-meta">
                                            <span><?= htmlspecialchars($it['artist']) ?></span>
                                            <span>× <?= (int) $it['quantity'] ?></span>
                                        </div>
                                    </div>
                                    <div class="summary-item-price">
                                        <?= number_format($it['subtotal'], 0, '', ' ') ?> ₽
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-card-foot">
                            <span class="order-foot-meta">
                                <?= htmlspecialchars($delivery_labels[$o['delivery_method']] ?? '') ?>
                                · <?= htmlspecialchars($payment_labels[$o['payment_method']] ?? '') ?>
                            </span>
                            <span class="order-foot-total">
                                Итого <strong><?= number_format($o['total'], 0, '', ' ') ?> ₽</strong>
                            </span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
