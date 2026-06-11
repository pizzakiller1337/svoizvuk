<?php
require_once __DIR__ . '/img_helpers.php';
session_start();
require 'db.php';
$link = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обновление количества — срабатывает и по кнопке «Обновить»,
    // и при авто-отправке формы через onchange у поля количества.
    if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $product_id => $qty) {
            $product_id = (int)$product_id;
            $qty        = (int)$qty;
            if ($qty > 0) {
                $_SESSION['cart'][$product_id] = $qty;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }
    if (isset($_POST['remove_item'])) {
        $product_id = (int)$_POST['remove_item'];
        unset($_SESSION['cart'][$product_id]);
    }
    header('Location: cart.php');
    exit;
}

$cart_items = [];
$total      = 0;

if (!empty($_SESSION['cart'])) {
    $ids          = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = mysqli_prepare($link, "SELECT * FROM products WHERE product_id IN ($placeholders)");
    mysqli_stmt_bind_param($stmt, str_repeat('i', count($ids)), ...$ids);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($product = mysqli_fetch_assoc($result)) {
        $id       = $product['product_id'];
        $qty      = (int)$_SESSION['cart'][$id];
        $subtotal = $product['price'] * $qty;
        $cart_items[] = ['product' => $product, 'quantity' => $qty, 'subtotal' => $subtotal];
        $total += $subtotal;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина — Свой звук</title>
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
    <link rel="stylesheet" href="styles.css?v=13">
</head>
<body>
<?php require 'header.php'; ?>
<main class="cart-page">
    <div class="container">
        <h1 class="page-title">Корзина</h1>

        <?php if (empty($cart_items)): ?>
            <div class="cart-empty">
                <p class="cart-empty-title">Ваша корзина пока пуста</p>
                <p class="cart-empty-sub">Самое время найти пластинку, которая останется с вами надолго.</p>
                <a href="index.php" class="btn btn-primary btn-buy">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <form method="POST" id="cart-form" class="cart-layout">

                <!-- Список товаров -->
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): $p = $item['product']; ?>
                    <div class="cart-item">
                        <a href="product.php?id=<?= (int)$p['product_id'] ?>" class="cart-item-cover">
                            <picture>
                                <?= webp_source($p['image_url']) ?>
                                <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                            </picture>
                        </a>

                        <div class="cart-item-info">
                            <a href="product.php?id=<?= (int)$p['product_id'] ?>" class="cart-item-title">
                                <?= htmlspecialchars($p['title']) ?>
                            </a>
                            <span class="cart-item-artist"><?= htmlspecialchars($p['artist']) ?></span>
                            <span class="cart-item-price-unit"><?= number_format($p['price'], 0, '', ' ') ?> ₽ / шт</span>
                        </div>

                        <div class="cart-item-qty">
                            <input type="number" name="quantity[<?= (int)$p['product_id'] ?>]"
                                   value="<?= (int)$item['quantity'] ?>" min="1" class="quantity-input"
                                   onchange="document.getElementById('cart-form').submit()">
                        </div>

                        <div class="cart-item-subtotal">
                            <?= number_format($item['subtotal'], 0, '', ' ') ?> ₽
                        </div>

                        <button type="submit" name="remove_item" value="<?= (int)$p['product_id'] ?>"
                                class="cart-item-remove" title="Удалить из корзины" aria-label="Удалить">
                            ✕
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Сводка заказа -->
                <aside class="cart-summary">
                    <h3 class="cart-summary-title">Сумма заказа</h3>

                    <div class="cart-summary-row">
                        <span>Товары (<?= count($cart_items) ?>)</span>
                        <span><?= number_format($total, 0, '', ' ') ?> ₽</span>
                    </div>
                    <div class="cart-summary-row">
                        <span>Доставка</span>
                        <span class="cart-summary-muted">на следующем шаге</span>
                    </div>

                    <div class="cart-summary-total">
                        <span>Итого</span>
                        <span class="cart-summary-amount"><?= number_format($total, 0, '', ' ') ?> ₽</span>
                    </div>

                    <?php if ($total < 3000): ?>
                        <p class="cart-summary-hint">
                            Бесплатная доставка от 3 000 ₽ — осталось добрать <?= number_format(3000 - $total, 0, '', ' ') ?> ₽
                        </p>
                    <?php endif; ?>

                    <a href="checkout.php" class="btn btn-primary summary-btn cart-checkout-btn">Оформить заказ</a>

                    <a href="index.php" class="cart-continue">← Продолжить покупки</a>
                </aside>

            </form>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>