<?php
session_start();

// Без данных о заказе — редирект на главную
if (empty($_SESSION['last_order'])) {
    header('Location: index.php');
    exit;
}

$order = $_SESSION['last_order'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ оформлен — Свой звук</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/assets/logo/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/logo/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/logo/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#171717">
    <link rel="stylesheet" href="styles.css?v=3">
</head>
<body>
<?php require 'header.php'; ?>

<main class="checkout-page">
    <div class="container">

        <ol class="checkout-steps">
            <li class="done"><span>1</span>Данные</li>
            <li class="done"><span>2</span>Оплата</li>
            <li class="active"><span>3</span>Готово</li>
        </ol>

        <section class="order-success">
            <div class="success-icon" aria-hidden="true">
                <svg viewBox="0 0 64 64" width="64" height="64" fill="none" stroke="currentColor"
                     stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="32" cy="32" r="29"></circle>
                    <path d="M19 33l9 9 17-19"></path>
                </svg>
            </div>
            <h2 class="success-title">Заказ оформлен</h2>
            <p class="success-subtitle">
                Спасибо! Мы отправили детали на
                <strong><?= htmlspecialchars($order['checkout']['email']) ?></strong>
            </p>

            <div class="order-card">
                <div class="order-card-header">
                    <div>
                        <div class="order-card-label">Номер заказа</div>
                        <div class="order-number"><?= htmlspecialchars($order['number']) ?></div>
                    </div>
                    <div class="order-card-meta">
                        <div class="order-card-label">Дата</div>
                        <div><?= htmlspecialchars($order['date']) ?></div>
                    </div>
                </div>

                <div class="order-card-body">
                    <div class="order-block">
                        <h4>Получатель</h4>
                        <p><?= htmlspecialchars($order['checkout']['name']) ?></p>
                        <p class="muted"><?= htmlspecialchars($order['checkout']['phone']) ?></p>
                    </div>

                    <div class="order-block">
                        <h4>Доставка</h4>
                        <?php if ($order['checkout']['delivery'] === 'pickup'): ?>
                            <p>Самовывоз</p>
                            <p class="muted">Москва, ул. Винильная, 33</p>
                        <?php else: ?>
                            <p><?= $order['checkout']['delivery'] === 'courier' ? 'Курьером' : 'Почта России' ?></p>
                            <p class="muted">
                                <?= htmlspecialchars(trim(
                                    ($order['checkout']['zip'] ? $order['checkout']['zip'] . ', ' : '') .
                                    $order['checkout']['city'] . ', ' . $order['checkout']['address'],
                                    ', '
                                )) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="order-block">
                        <h4>Оплата</h4>
                        <p>
                            <?= htmlspecialchars($order['method_label']) ?>
                            <?php if (!empty($order['card_last4'])): ?>
                                <span class="muted"> · •••• <?= htmlspecialchars($order['card_last4']) ?></span>
                            <?php endif; ?>
                        </p>
                        <p class="muted">
                            <?= $order['method'] === 'cash' ? 'Будет принята при получении' : 'Платёж проведён' ?>
                        </p>
                    </div>
                </div>

                <div class="order-items">
                    <?php foreach ($order['items'] as $it): ?>
                        <div class="summary-item">
                            <img src="<?= htmlspecialchars($it['image']) ?>"
                                 alt="<?= htmlspecialchars($it['title']) ?>">
                            <div class="summary-item-info">
                                <div class="summary-item-title"><?= htmlspecialchars($it['title']) ?></div>
                                <div class="summary-item-meta">
                                    <span><?= htmlspecialchars($it['artist']) ?></span>
                                    <span>× <?= (int)$it['quantity'] ?></span>
                                </div>
                            </div>
                            <div class="summary-item-price">
                                <?= number_format($it['price'] * $it['quantity'], 0, '', ' ') ?> ₽
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-totals">
                    <div class="summary-row">
                        <span>Товары</span>
                        <span><?= number_format($order['subtotal'], 0, '', ' ') ?> ₽</span>
                    </div>
                    <div class="summary-row">
                        <span>Доставка</span>
                        <span>
                            <?= $order['delivery_cost'] === 0
                                ? 'Бесплатно'
                                : number_format($order['delivery_cost'], 0, '', ' ') . ' ₽' ?>
                        </span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Итого</span>
                        <span><?= number_format($order['total'], 0, '', ' ') ?> ₽</span>
                    </div>
                </div>
            </div>

            <div class="success-actions">
                <a href="index.php" class="btn-checkout">Вернуться в каталог</a>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>