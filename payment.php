<?php
session_start();
require 'db.php';
$link = get_db();

// Проверяем, что пользователь прошёл шаг 1 и есть товары
if (empty($_SESSION['cart']) || empty($_SESSION['checkout'])) {
    header('Location: checkout.php');
    exit;
}

$checkout = $_SESSION['checkout'];

// Считаем итог
$cart_items = [];
$total      = 0;
$ids          = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = mysqli_prepare($link, "SELECT * FROM products WHERE product_id IN ($placeholders)");
mysqli_stmt_bind_param($stmt, str_repeat('i', count($ids)), ...$ids);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($product = mysqli_fetch_assoc($result)) {
    $id  = $product['product_id'];
    $qty = (int)$_SESSION['cart'][$id];
    $cart_items[] = ['product' => $product, 'quantity' => $qty];
    $total += $product['price'] * $qty;
}

$delivery_cost = 0;
if ($checkout['delivery'] === 'courier') $delivery_cost = $total >= 3000 ? 0 : 350;
if ($checkout['delivery'] === 'post')    $delivery_cost = $total >= 3000 ? 0 : 250;
$grand_total = $total + $delivery_cost;

// Обработка имитированной оплаты
$payment_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'] ?? '';

    $valid_method = in_array($method, ['card', 'sbp', 'cash'], true);
    if (!$valid_method) {
        $payment_error = 'Выберите способ оплаты';
    } elseif ($method === 'card') {
        // Имитация валидации карточных данных
        $number = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
        $exp    = trim($_POST['card_exp']    ?? '');
        $cvv    = preg_replace('/\D/', '', $_POST['card_cvv']  ?? '');
        $holder = trim($_POST['card_holder'] ?? '');

        if (strlen($number) < 13 || strlen($number) > 19) {
            $payment_error = 'Некорректный номер карты';
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $exp)) {
            $payment_error = 'Срок действия в формате ММ/ГГ';
        } elseif (strlen($cvv) !== 3) {
            $payment_error = 'CVV должен содержать 3 цифры';
        } elseif (mb_strlen($holder) < 2) {
            $payment_error = 'Укажите имя владельца';
        } else {
            // Имитация: «карта 4111...» всегда успешна, «4000 0000 0000 0002» — отказ
            if (strpos($number, '4000000000000002') === 0) {
                $payment_error = 'Платёж отклонён банком. Попробуйте другую карту.';
            }
        }
    }

    if (!$payment_error) {
        // Генерируем номер заказа
        $order_number = 'SZ-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 5));
        $card_last4   = $method === 'card'
            ? substr(preg_replace('/\D/', '', $_POST['card_number'] ?? ''), -4)
            : null;
        $payment_status = $method === 'cash' ? 'pending' : 'paid';
        $user_id        = $_SESSION['user_id'] ?? null;

        // Сохраняем заказ в БД (в транзакции)
        mysqli_begin_transaction($link);
        try {
            $stmt = mysqli_prepare($link,
                "INSERT INTO orders
                 (order_number, user_id, customer_name, customer_email, customer_phone,
                  delivery_method, delivery_city, delivery_zip, delivery_address, delivery_cost,
                  comment, payment_method, payment_card_last4, payment_status,
                  subtotal, total, status)
                 VALUES (?, ?, ?, ?, ?,  ?, ?, ?, ?, ?,  ?, ?, ?, ?,  ?, ?, 'new')"
            );
            mysqli_stmt_bind_param(
                $stmt,
                'sisssssssdssssdd',
                $order_number, $user_id, $checkout['name'], $checkout['email'], $checkout['phone'],
                $checkout['delivery'], $checkout['city'], $checkout['zip'], $checkout['address'],
                $delivery_cost, $checkout['comment'], $method, $card_last4, $payment_status,
                $total, $grand_total
            );
            mysqli_stmt_execute($stmt);
            $order_id = mysqli_insert_id($link);

            $stmt_item = mysqli_prepare($link,
                "INSERT INTO order_items
                 (order_id, product_id, title, artist, image_url, price, quantity, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            foreach ($cart_items as $i) {
                $pid       = (int)$i['product']['product_id'];
                $title     = $i['product']['title'];
                $artist    = $i['product']['artist'];
                $img       = $i['product']['image_url'];
                $price     = (float)$i['product']['price'];
                $qty       = (int)$i['quantity'];
                $subtotal  = $price * $qty;
                mysqli_stmt_bind_param(
                    $stmt_item, 'iisssdid',
                    $order_id, $pid, $title, $artist, $img, $price, $qty, $subtotal
                );
                mysqli_stmt_execute($stmt_item);
            }
            mysqli_commit($link);
        } catch (Throwable $e) {
            mysqli_rollback($link);
            $payment_error = 'Не удалось сохранить заказ. Попробуйте ещё раз.';
        }
    }

    if (!$payment_error) {
        // Сохраняем краткую копию для страницы успеха
        $_SESSION['last_order'] = [
            'number'        => $order_number,
            'date'          => date('d.m.Y H:i'),
            'method'        => $method,
            'method_label'  => match ($method) {
                'card' => 'Банковская карта',
                'sbp'  => 'СБП',
                'cash' => 'При получении',
                default => 'Не указан',
            },
            'card_last4'    => $card_last4,
            'checkout'      => $checkout,
            'items'         => array_map(fn($i) => [
                'title'    => $i['product']['title'],
                'artist'   => $i['product']['artist'],
                'image'    => $i['product']['image_url'],
                'price'    => (float)$i['product']['price'],
                'quantity' => $i['quantity'],
            ], $cart_items),
            'subtotal'      => $total,
            'delivery_cost' => $delivery_cost,
            'total'         => $grand_total,
        ];

        // Чистим корзину и checkout
        unset($_SESSION['cart'], $_SESSION['checkout']);

        header('Location: order_success.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оплата — Свой звук</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/assets/logo/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/logo/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/logo/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#171717">
    <link rel="stylesheet" href="styles.css?v=5">
</head>
<body>
<?php require 'header.php'; ?>

<main class="checkout-page">
    <div class="container">
        <h2 class="section-title">Оплата заказа</h2>

        <ol class="checkout-steps">
            <li class="done"><span>1</span>Данные</li>
            <li class="active"><span>2</span>Оплата</li>
            <li><span>3</span>Готово</li>
        </ol>

        <?php if ($payment_error): ?>
            <div class="message error"><?= htmlspecialchars($payment_error) ?></div>
        <?php endif; ?>

        <form method="POST" action="payment.php" class="checkout-form" id="payment-form" novalidate>
            <div class="checkout-grid">
                <div class="checkout-main">

                    <section class="checkout-block">
                        <h3 class="checkout-block-title">Способ оплаты</h3>
                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="card" checked>
                                <div class="payment-card">
                                    <div class="payment-name">Банковская карта</div>
                                    <div class="payment-desc">Visa, MasterCard, МИР</div>
                                </div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="sbp">
                                <div class="payment-card">
                                    <div class="payment-name">СБП</div>
                                    <div class="payment-desc">По QR-коду через банк</div>
                                </div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cash">
                                <div class="payment-card">
                                    <div class="payment-name">При получении</div>
                                    <div class="payment-desc">Наличные или картой</div>
                                </div>
                            </label>
                        </div>
                    </section>

                    <section class="checkout-block payment-details" data-for="card">
                        <h3 class="checkout-block-title">Данные карты</h3>

                        <div class="card-mock" id="card-mock">
                            <div class="card-mock-chip"></div>
                            <div class="card-mock-brand" id="card-brand">CARD</div>
                            <div class="card-mock-number" id="card-mock-number">•••• •••• •••• ••••</div>
                            <div class="card-mock-row">
                                <div>
                                    <div class="card-mock-label">Владелец</div>
                                    <div class="card-mock-value" id="card-mock-holder">ВАШЕ ИМЯ</div>
                                </div>
                                <div>
                                    <div class="card-mock-label">Срок</div>
                                    <div class="card-mock-value" id="card-mock-exp">ММ/ГГ</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="card_number">Номер карты</label>
                            <input type="text" id="card_number" name="card_number"
                                   class="form-input" inputmode="numeric"
                                   placeholder="0000 0000 0000 0000" maxlength="23" autocomplete="cc-number">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="card_exp">Срок (ММ/ГГ)</label>
                                <input type="text" id="card_exp" name="card_exp"
                                       class="form-input" placeholder="ММ/ГГ" maxlength="5"
                                       inputmode="numeric" autocomplete="cc-exp">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="card_cvv">CVV / CVC</label>
                                <input type="password" id="card_cvv" name="card_cvv"
                                       class="form-input" maxlength="3"
                                       inputmode="numeric" autocomplete="cc-csc">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="card_holder">Имя владельца</label>
                            <input type="text" id="card_holder" name="card_holder"
                                   class="form-input" placeholder="IVAN IVANOV" autocomplete="cc-name">
                        </div>

                        <p class="payment-hint">
                            Это демонстрационная страница. Введите любые тестовые данные —
                            например, <code>4111 1111 1111 1111</code>, любой будущий срок и любые 3 цифры.
                        </p>
                    </section>

                    <section class="checkout-block payment-details" data-for="sbp" style="display:none;">
                        <h3 class="checkout-block-title">Оплата через СБП</h3>
                        <div class="sbp-block">
                            <div class="sbp-qr" aria-hidden="true">
                                <div class="sbp-qr-grid">
                                    <?php for ($i = 0; $i < 144; $i++): ?>
                                        <span style="opacity:<?= rand(0, 1) ? '1' : '0' ?>"></span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="sbp-text">
                                <p>Отсканируйте QR-код в приложении вашего банка для оплаты по СБП.</p>
                                <p class="payment-hint">
                                    Это демонстрация. Нажмите «Оплатить» — заказ оформится без реальной транзакции.
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="checkout-block payment-details" data-for="cash" style="display:none;">
                        <h3 class="checkout-block-title">Оплата при получении</h3>
                        <p>Вы оплатите заказ наличными или картой курьеру / в пункте выдачи.</p>
                        <p class="payment-hint">
                            После подтверждения мы зарезервируем пластинки и отправим деталями на почту
                            <strong><?= htmlspecialchars($checkout['email']) ?></strong>.
                        </p>
                    </section>

                </div>

                <aside class="checkout-summary">
                    <div class="summary-inner">
                        <h3 class="summary-title">К оплате</h3>
                        <div class="summary-rows">
                            <div class="summary-row">
                                <span>Товары</span>
                                <span><?= number_format($total, 0, '', ' ') ?> ₽</span>
                            </div>
                            <div class="summary-row">
                                <span>Доставка</span>
                                <span><?= $delivery_cost === 0 ? 'Бесплатно' : number_format($delivery_cost, 0, '', ' ') . ' ₽' ?></span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Итого</span>
                                <span><?= number_format($grand_total, 0, '', ' ') ?> ₽</span>
                            </div>
                        </div>

                        <div class="summary-recipient">
                            <div class="summary-recipient-title">Получатель</div>
                            <div><?= htmlspecialchars($checkout['name']) ?></div>
                            <div class="summary-muted"><?= htmlspecialchars($checkout['phone']) ?></div>
                            <?php if ($checkout['delivery'] !== 'pickup'): ?>
                                <div class="summary-muted">
                                    <?= htmlspecialchars(trim($checkout['city'] . ', ' . $checkout['address'], ', ')) ?>
                                </div>
                            <?php else: ?>
                                <div class="summary-muted">Самовывоз: ул. Винильная, 33</div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn-checkout summary-btn" id="pay-btn">
                            Оплатить <?= number_format($grand_total, 0, '', ' ') ?> ₽
                        </button>
                        <a href="checkout.php" class="summary-back">← Изменить данные</a>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</main>

<!-- Оверлей «обработка платежа» -->
<div class="payment-overlay" id="payment-overlay" hidden>
    <div class="payment-overlay-inner">
        <div class="vinyl-spinner" aria-hidden="true">
            <div class="vinyl-spinner-disc">
                <div class="vinyl-spinner-label"></div>
            </div>
        </div>
        <p class="payment-overlay-text" id="payment-overlay-text">Соединение с банком…</p>
        <p class="payment-overlay-sub">Не закрывайте страницу</p>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

<script>
(function() {
    // Переключение блоков по способу оплаты
    const blocks = document.querySelectorAll('.payment-details');
    document.querySelectorAll('input[name="payment_method"]').forEach(r => {
        r.addEventListener('change', () => {
            blocks.forEach(b => b.style.display = (b.dataset.for === r.value) ? '' : 'none');
        });
    });

    // Маска номера карты + определение бренда
    const num    = document.getElementById('card_number');
    const exp    = document.getElementById('card_exp');
    const cvv    = document.getElementById('card_cvv');
    const holder = document.getElementById('card_holder');
    const mockNum    = document.getElementById('card-mock-number');
    const mockExp    = document.getElementById('card-mock-exp');
    const mockHolder = document.getElementById('card-mock-holder');
    const mockBrand  = document.getElementById('card-brand');

    function detectBrand(n) {
        if (/^4/.test(n))           return 'VISA';
        if (/^(5[1-5]|2[2-7])/.test(n)) return 'MASTERCARD';
        if (/^(2200|2201|2202|2203|2204)/.test(n)) return 'МИР';
        if (/^3[47]/.test(n))       return 'AMEX';
        return 'CARD';
    }

    if (num) {
        num.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, '').slice(0, 19);
            const groups = v.match(/.{1,4}/g);
            e.target.value = groups ? groups.join(' ') : '';
            mockNum.textContent = (e.target.value + ' •••• •••• •••• ••••').slice(0, 19);
            mockBrand.textContent = detectBrand(v);
        });
    }
    if (exp) {
        exp.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, '').slice(0, 4);
            if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
            e.target.value = v;
            mockExp.textContent = v || 'ММ/ГГ';
        });
    }
    if (cvv) {
        cvv.addEventListener('input', e => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3);
        });
    }
    if (holder) {
        holder.addEventListener('input', e => {
            e.target.value = e.target.value.toUpperCase().replace(/[^A-ZА-ЯЁ\s\-]/g, '');
            mockHolder.textContent = e.target.value || 'ВАШЕ ИМЯ';
        });
    }

    // Имитация обработки платежа перед отправкой формы
    const form    = document.getElementById('payment-form');
    const overlay = document.getElementById('payment-overlay');
    const overlayText = document.getElementById('payment-overlay-text');
    const payBtn  = document.getElementById('pay-btn');

    const stages = {
        card: ['Соединение с банком…', 'Подтверждение оплаты…', 'Списание средств…'],
        sbp:  ['Передача данных в СБП…', 'Ожидание подтверждения…', 'Платёж получен…'],
        cash: ['Резервирование товаров…', 'Подтверждение заказа…']
    };

    if (form && overlay) {
        form.addEventListener('submit', e => {
            const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'card';
            const seq = stages[method] || stages.card;

            // Если carda — небольшая клиентская валидация перед показом оверлея
            if (method === 'card') {
                const numV = (num?.value || '').replace(/\D/g, '');
                const expV = exp?.value || '';
                const cvvV = cvv?.value || '';
                const holV = holder?.value || '';
                if (numV.length < 13 || !/^(0[1-9]|1[0-2])\/\d{2}$/.test(expV) || cvvV.length !== 3 || holV.length < 2) {
                    return; // даём серверу выдать ошибку
                }
            }

            e.preventDefault();
            payBtn.disabled = true;
            overlay.hidden = false;

            let i = 0;
            overlayText.textContent = seq[0];
            const t = setInterval(() => {
                i++;
                if (i < seq.length) {
                    overlayText.textContent = seq[i];
                } else {
                    clearInterval(t);
                    form.submit();
                }
            }, 900);
        });
    }
})();
</script>
</body>
</html>