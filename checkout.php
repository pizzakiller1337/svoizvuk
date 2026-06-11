<?php
session_start();
require 'db.php';
$link = get_db();

// Если корзина пуста — отправляем обратно
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$errors = [];

// Префилл данных, если пользователь авторизован
$prefill = [
    'name'    => $_SESSION['username'] ?? '',
    'email'   => $_SESSION['email']    ?? '',
    'phone'   => '',
    'city'    => '',
    'address' => '',
    'zip'     => '',
    'comment' => '',
    'delivery'=> 'courier',
];

// Восстановление из сессии, если пользователь возвращался назад
if (!empty($_SESSION['checkout'])) {
    $prefill = array_merge($prefill, $_SESSION['checkout']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'     => trim($_POST['name']     ?? ''),
        'email'    => trim($_POST['email']    ?? ''),
        'phone'    => trim($_POST['phone']    ?? ''),
        'city'     => trim($_POST['city']     ?? ''),
        'address'  => trim($_POST['address']  ?? ''),
        'zip'      => trim($_POST['zip']      ?? ''),
        'comment'  => trim($_POST['comment']  ?? ''),
        'delivery' => $_POST['delivery']      ?? 'courier',
    ];

    // Валидация
    if ($data['name'] === '' || mb_strlen($data['name']) < 2) {
        $errors['name'] = 'Укажите имя получателя';
    }
    if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Укажите корректный email';
    }
    if ($data['phone'] === '' || !preg_match('/^[\d\s\+\-\(\)]{10,20}$/', $data['phone'])) {
        $errors['phone'] = 'Укажите корректный телефон';
    }

    // Адрес обязателен только для доставки
    if ($data['delivery'] !== 'pickup') {
        if ($data['city'] === '')    $errors['city']    = 'Укажите город';
        if ($data['address'] === '') $errors['address'] = 'Укажите адрес доставки';
    }

    if (!in_array($data['delivery'], ['courier', 'post', 'pickup'], true)) {
        $data['delivery'] = 'courier';
    }

    $prefill = array_merge($prefill, $data);

    if (empty($errors)) {
        $_SESSION['checkout'] = $data;
        header('Location: payment.php');
        exit;
    }
}

// Загружаем товары корзины для итоговой суммы
$cart_items = [];
$total      = 0;
$total_qty  = 0;

if (!empty($_SESSION['cart'])) {
    $ids          = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = mysqli_prepare($link, "SELECT * FROM products WHERE product_id IN ($placeholders)");
    mysqli_stmt_bind_param($stmt, str_repeat('i', count($ids)), ...$ids);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($product = mysqli_fetch_assoc($result)) {
        $id        = $product['product_id'];
        $qty       = (int)$_SESSION['cart'][$id];
        $subtotal  = $product['price'] * $qty;
        $cart_items[] = ['product' => $product, 'quantity' => $qty, 'subtotal' => $subtotal];
        $total     += $subtotal;
        $total_qty += $qty;
    }
}

// Стоимость доставки
$delivery_cost = 0;
$delivery_method = $prefill['delivery'];
if ($delivery_method === 'courier') {
    $delivery_cost = $total >= 3000 ? 0 : 350;
} elseif ($delivery_method === 'post') {
    $delivery_cost = $total >= 3000 ? 0 : 250;
}
$grand_total = $total + $delivery_cost;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа — Свой звук</title>
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

<main class="checkout-page">
    <div class="container">
        <h1 class="page-title">Оформление заказа</h1>

        <ol class="checkout-steps">
            <li class="active"><span>1</span>Данные</li>
            <li><span>2</span>Оплата</li>
            <li><span>3</span>Готово</li>
        </ol>

        <form method="POST" action="checkout.php" class="checkout-form" novalidate>
            <div class="checkout-grid">
                <div class="checkout-main">

                    <section class="checkout-block">
                        <h3 class="checkout-block-title">Контактные данные</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="name">Имя получателя</label>
                                <input type="text" id="name" name="name" class="form-input"
                                       value="<?= htmlspecialchars($prefill['name']) ?>" required>
                                <?php if (!empty($errors['name'])): ?>
                                    <small class="field-error"><?= htmlspecialchars($errors['name']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="phone">Телефон</label>
                                <input type="tel" id="phone" name="phone" class="form-input"
                                       value="<?= htmlspecialchars($prefill['phone']) ?>"
                                       placeholder="+7 (___) ___-__-__" required>
                                <?php if (!empty($errors['phone'])): ?>
                                    <small class="field-error"><?= htmlspecialchars($errors['phone']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-input"
                                   value="<?= htmlspecialchars($prefill['email']) ?>" required>
                            <?php if (!empty($errors['email'])): ?>
                                <small class="field-error"><?= htmlspecialchars($errors['email']) ?></small>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="checkout-block">
                        <h3 class="checkout-block-title">Способ доставки</h3>
                        <div class="delivery-options">
                            <label class="delivery-option">
                                <input type="radio" name="delivery" value="courier"
                                    <?= $prefill['delivery'] === 'courier' ? 'checked' : '' ?>>
                                <div class="delivery-card">
                                    <div class="delivery-name">Курьером</div>
                                    <div class="delivery-desc">1–3 дня по городу</div>
                                    <div class="delivery-price">
                                        <?= $total >= 3000 ? 'Бесплатно' : '350 ₽' ?>
                                    </div>
                                </div>
                            </label>
                            <label class="delivery-option">
                                <input type="radio" name="delivery" value="post"
                                    <?= $prefill['delivery'] === 'post' ? 'checked' : '' ?>>
                                <div class="delivery-card">
                                    <div class="delivery-name">Почта России</div>
                                    <div class="delivery-desc">5–14 дней по РФ</div>
                                    <div class="delivery-price">
                                        <?= $total >= 3000 ? 'Бесплатно' : '250 ₽' ?>
                                    </div>
                                </div>
                            </label>
                            <label class="delivery-option">
                                <input type="radio" name="delivery" value="pickup"
                                    <?= $prefill['delivery'] === 'pickup' ? 'checked' : '' ?>>
                                <div class="delivery-card">
                                    <div class="delivery-name">Самовывоз</div>
                                    <div class="delivery-desc">Москва, ул. Винильная, 33</div>
                                    <div class="delivery-price">Бесплатно</div>
                                </div>
                            </label>
                        </div>
                    </section>

                    <section class="checkout-block address-block"
                             <?= $prefill['delivery'] === 'pickup' ? 'style="display:none;"' : '' ?>>
                        <h3 class="checkout-block-title">Адрес доставки</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="city">Город</label>
                                <input type="text" id="city" name="city" class="form-input"
                                       value="<?= htmlspecialchars($prefill['city']) ?>">
                                <?php if (!empty($errors['city'])): ?>
                                    <small class="field-error"><?= htmlspecialchars($errors['city']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="form-group form-group-zip">
                                <label class="form-label" for="zip">Индекс</label>
                                <input type="text" id="zip" name="zip" class="form-input"
                                       value="<?= htmlspecialchars($prefill['zip']) ?>" maxlength="6">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="address">Улица, дом, квартира</label>
                            <input type="text" id="address" name="address" class="form-input"
                                   value="<?= htmlspecialchars($prefill['address']) ?>">
                            <?php if (!empty($errors['address'])): ?>
                                <small class="field-error"><?= htmlspecialchars($errors['address']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="comment">Комментарий к заказу</label>
                            <textarea id="comment" name="comment" class="form-input"
                                      rows="3" placeholder="Например, домофон или удобное время"><?= htmlspecialchars($prefill['comment']) ?></textarea>
                        </div>
                    </section>

                </div>

                <aside class="checkout-summary">
                    <div class="summary-inner">
                        <h3 class="summary-title">Ваш заказ</h3>
                        <div class="summary-items">
                            <?php foreach ($cart_items as $item): $p = $item['product']; ?>
                                <div class="summary-item">
                                    <img src="<?= htmlspecialchars($p['image_url']) ?>"
                                         alt="<?= htmlspecialchars($p['title']) ?>">
                                    <div class="summary-item-info">
                                        <div class="summary-item-title"><?= htmlspecialchars($p['title']) ?></div>
                                        <div class="summary-item-meta">
                                            <span><?= htmlspecialchars($p['artist']) ?></span>
                                            <span>× <?= (int)$item['quantity'] ?></span>
                                        </div>
                                    </div>
                                    <div class="summary-item-price">
                                        <?= number_format($item['subtotal'], 0, '', ' ') ?> ₽
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary-rows">
                            <div class="summary-row">
                                <span>Товары (<?= $total_qty ?>)</span>
                                <span><?= number_format($total, 0, '', ' ') ?> ₽</span>
                            </div>
                            <div class="summary-row" id="summary-delivery">
                                <span>Доставка</span>
                                <span data-cost="<?= $delivery_cost ?>">
                                    <?= $delivery_cost === 0 ? 'Бесплатно' : number_format($delivery_cost, 0, '', ' ') . ' ₽' ?>
                                </span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Итого</span>
                                <span id="summary-grand-total">
                                    <?= number_format($grand_total, 0, '', ' ') ?> ₽
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary summary-btn">Перейти к оплате</button>
                        <a href="cart.php" class="summary-back">← Вернуться в корзину</a>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>

<script>
(function() {
    // Маска для телефона
    const phone = document.getElementById('phone');
    if (phone) {
        phone.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, '');
            if (v.startsWith('8')) v = '7' + v.slice(1);
            if (!v.startsWith('7')) v = '7' + v;
            v = v.slice(0, 11);
            const p = ['+', v[0] || ''];
            if (v.length > 1) p.push(' (', v.slice(1, 4));
            if (v.length >= 4) p.push(') ', v.slice(4, 7));
            if (v.length >= 7) p.push('-', v.slice(7, 9));
            if (v.length >= 9) p.push('-', v.slice(9, 11));
            e.target.value = p.join('');
        });
    }

    // Только цифры в индексе
    const zip = document.getElementById('zip');
    if (zip) {
        zip.addEventListener('input', e => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
        });
    }

    // Скрытие блока адреса для самовывоза + пересчёт доставки
    const cartTotal = <?= (int)$total ?>;
    const freeFrom  = 3000;
    const costs     = { courier: cartTotal >= freeFrom ? 0 : 350,
                        post:    cartTotal >= freeFrom ? 0 : 250,
                        pickup:  0 };

    const addressBlock = document.querySelector('.address-block');
    const deliveryRow  = document.querySelector('#summary-delivery span[data-cost]');
    const grandTotal   = document.getElementById('summary-grand-total');

    document.querySelectorAll('input[name="delivery"]').forEach(r => {
        r.addEventListener('change', () => {
            const v = r.value;
            if (addressBlock) addressBlock.style.display = (v === 'pickup') ? 'none' : '';
            const c = costs[v] ?? 0;
            if (deliveryRow) {
                deliveryRow.dataset.cost = c;
                deliveryRow.textContent = c === 0 ? 'Бесплатно' : c.toLocaleString('ru-RU') + ' ₽';
            }
            if (grandTotal) {
                grandTotal.textContent = (cartTotal + c).toLocaleString('ru-RU') + ' ₽';
            }
        });
    });
})();
</script>
</body>
</html>