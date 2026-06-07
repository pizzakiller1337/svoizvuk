<?php
require_once __DIR__ . '/../includes/auth.php';
$link = get_db();
require_once __DIR__ . '/../includes/orders_schema.php';
requireAdmin();

// Проверяем схему — если не в порядке, показываем сообщение и выходим
$orders_schema = ordersSchemaStatus($link);
if (!$orders_schema['ok']) {
    $page_title   = 'Заказы';
    $current_page = 'orders';
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="alert alert-error">' . ordersSchemaErrorHtml($orders_schema) . '</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}

$status_labels = [
    'new'        => 'Новый',
    'processing' => 'В обработке',
    'shipped'    => 'Отправлен',
    'delivered'  => 'Доставлен',
    'cancelled'  => 'Отменён',
];
$payment_labels = [
    'pending' => 'Ожидает',
    'paid'    => 'Оплачен',
    'failed'  => 'Ошибка',
];
$method_labels = [
    'card' => 'Банковская карта',
    'sbp'  => 'СБП',
    'cash' => 'При получении',
];
$delivery_labels = [
    'courier' => 'Курьером',
    'post'    => 'Почта России',
    'pickup'  => 'Самовывоз',
];

$success = '';
$error   = '';

// Обновление статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'] ?? '';
    if (!isset($status_labels[$new_status])) {
        $error = 'Некорректный статус';
    } else {
        $stmt = mysqli_prepare($link, "UPDATE orders SET status = ? WHERE order_id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $new_status, $order_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Статус обновлён';
        } else {
            $error = 'Не удалось обновить статус';
        }
    }
}

// Заказ
$stmt = mysqli_prepare($link, "SELECT * FROM orders WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    header('Location: index.php');
    exit;
}

// Позиции заказа
$stmt = mysqli_prepare($link, "SELECT * FROM order_items WHERE order_id = ? ORDER BY item_id");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$items = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

$page_title   = 'Заказ ' . $order['order_number'];
$current_page = 'orders';

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card-header" style="background:none;padding:0;margin-bottom:20px;">
    <div>
        <a href="index.php" class="btn btn-edit btn-sm">← К списку</a>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <span style="color:#666;font-size:0.85rem;">
            Создан: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
        </span>
    </div>
</div>

<div class="order-grid">
    <!-- Левая колонка -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <div class="card">
            <div class="card-header">
                <h3>
                    Заказ
                    <span style="font-family:'Courier New',monospace;color:#fff;margin-left:8px;">
                        <?= htmlspecialchars($order['order_number']) ?>
                    </span>
                </h3>
                <span class="badge badge-status badge-status-<?= htmlspecialchars($order['status']) ?>">
                    <?= htmlspecialchars($status_labels[$order['status']] ?? $order['status']) ?>
                </span>
            </div>
            <div class="order-info-grid">
                <div>
                    <div class="label">Покупатель</div>
                    <div class="value"><?= htmlspecialchars($order['customer_name']) ?></div>
                    <div class="muted"><?= htmlspecialchars($order['customer_email']) ?></div>
                    <div class="muted"><?= htmlspecialchars($order['customer_phone']) ?></div>
                </div>
                <div>
                    <div class="label">Доставка</div>
                    <div class="value"><?= htmlspecialchars($delivery_labels[$order['delivery_method']] ?? '—') ?></div>
                    <?php if ($order['delivery_method'] !== 'pickup'): ?>
                        <div class="muted">
                            <?= htmlspecialchars(trim(
                                ($order['delivery_zip'] ? $order['delivery_zip'] . ', ' : '') .
                                ($order['delivery_city'] ?? '') . ', ' .
                                ($order['delivery_address'] ?? ''),
                                ', '
                            )) ?>
                        </div>
                    <?php else: ?>
                        <div class="muted">Москва, ул. Винильная, 33</div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="label">Оплата</div>
                    <div class="value"><?= htmlspecialchars($method_labels[$order['payment_method']] ?? '—') ?></div>
                    <?php if (!empty($order['payment_card_last4'])): ?>
                        <div class="muted">•••• <?= htmlspecialchars($order['payment_card_last4']) ?></div>
                    <?php endif; ?>
                    <div style="margin-top:6px;">
                        <span class="badge badge-pay-<?= htmlspecialchars($order['payment_status']) ?>">
                            <?= htmlspecialchars($payment_labels[$order['payment_status']] ?? $order['payment_status']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if (!empty($order['comment'])): ?>
                <div style="padding:0 24px 24px;">
                    <div class="label" style="font-size:0.75rem;color:#666;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">
                        Комментарий
                    </div>
                    <div style="background:#252525;padding:12px 14px;border-radius:6px;color:#ccc;">
                        <?= nl2br(htmlspecialchars($order['comment'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Состав заказа</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Обложка</th>
                        <th>Пластинка</th>
                        <th style="text-align:center;">Кол-во</th>
                        <th style="text-align:right;">Цена</th>
                        <th style="text-align:right;">Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td>
                                <?php if ($it['image_url']): ?>
                                    <img src="<?= htmlspecialchars($it['image_url']) ?>" class="product-img" alt="">
                                <?php else: ?>
                                    <div style="width:48px;height:48px;background:#2a2a2a;border-radius:6px;"></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="color:#fff;"><?= htmlspecialchars($it['title']) ?></div>
                                <div style="color:#666;font-size:0.82rem;"><?= htmlspecialchars($it['artist']) ?></div>
                                <?php if (!empty($it['product_id'])): ?>
                                    <a href="/admin/products/edit.php?id=<?= (int)$it['product_id'] ?>"
                                       style="color:#666;font-size:0.75rem;text-decoration:none;">
                                        К товару →
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;"><?= (int)$it['quantity'] ?></td>
                            <td style="text-align:right;color:#888;">
                                <?= number_format($it['price'], 0, '', ' ') ?> ₽
                            </td>
                            <td style="text-align:right;font-weight:600;color:#fff;">
                                <?= number_format($it['subtotal'], 0, '', ' ') ?> ₽
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="border-top:1px solid #2a2a2a;">
                <div class="order-totals-row">
                    <span style="color:#888;">Товары</span>
                    <span><?= number_format($order['subtotal'], 0, '', ' ') ?> ₽</span>
                </div>
                <div class="order-totals-row">
                    <span style="color:#888;">Доставка</span>
                    <span>
                        <?= $order['delivery_cost'] == 0
                            ? 'Бесплатно'
                            : number_format($order['delivery_cost'], 0, '', ' ') . ' ₽' ?>
                    </span>
                </div>
                <div class="order-totals-row grand">
                    <span>Итого</span>
                    <span><?= number_format($order['total'], 0, '', ' ') ?> ₽</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Правая колонка — управление -->
    <aside style="display:flex;flex-direction:column;gap:16px;">
        <div class="status-form">
            <h4>Статус заказа</h4>

            <div class="status-current">
                <span class="badge badge-status badge-status-<?= htmlspecialchars($order['status']) ?>">
                    <?= htmlspecialchars($status_labels[$order['status']] ?? $order['status']) ?>
                </span>
            </div>

            <form method="POST">
                <select name="status">
                    <?php foreach ($status_labels as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $order['status'] === $k ? 'selected' : '' ?>>
                            <?= $v ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="update_status" class="btn btn-primary">
                    Сохранить
                </button>
            </form>
        </div>

        <div class="status-form">
            <h4>Контакты покупателя</h4>
            <div style="margin-bottom:10px;">
                <a href="mailto:<?= htmlspecialchars($order['customer_email']) ?>"
                   style="color:#6db7ff;text-decoration:none;font-size:0.9rem;">
                    <?= htmlspecialchars($order['customer_email']) ?>
                </a>
            </div>
            <div>
                <a href="tel:<?= htmlspecialchars(preg_replace('/\s/', '', $order['customer_phone'])) ?>"
                   style="color:#6db7ff;text-decoration:none;font-size:0.9rem;">
                    <?= htmlspecialchars($order['customer_phone']) ?>
                </a>
            </div>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
