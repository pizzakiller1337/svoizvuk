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
$return_labels = [
    'none'      => 'Без возврата',
    'requested' => 'Запрошен',
    'approved'  => 'Одобрен',
    'rejected'  => 'Отклонён',
    'refunded'  => 'Деньги возвращены',
];
// Цвет бейджа возврата — переиспользуем палитру статусов заказа
$return_badge_class = [
    'requested' => 'badge-status-processing',
    'approved'  => 'badge-status-shipped',
    'rejected'  => 'badge-status-cancelled',
    'refunded'  => 'badge-status-delivered',
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

// Решение по возврату
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_return'])) {
    $new_return = $_POST['return_status'] ?? '';
    $allowed_return = ['requested', 'approved', 'rejected', 'refunded'];
    if (!in_array($new_return, $allowed_return, true)) {
        $error = 'Некорректный статус возврата';
    } else {
        $stmt = mysqli_prepare($link, "UPDATE orders SET return_status = ? WHERE order_id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $new_return, $order_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Статус возврата обновлён';
        } else {
            $error = 'Не удалось обновить возврат';
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

<div class="card-header card-header-plain">
    <div>
        <a href="index.php" class="btn btn-edit btn-sm">← К списку</a>
    </div>
    <div class="u-row">
        <span class="u-muted u-sm">
            Создан: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
        </span>
    </div>
</div>

<div class="order-grid">
    <!-- Левая колонка -->
    <div class="u-col">

        <div class="card">
            <div class="card-header">
                <h3>
                    Заказ
                    <span class="u-mono u-white" style="margin-left:8px;">
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
                    <div class="label order-block-title">
                        Комментарий
                    </div>
                    <div class="note-box">
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
                        <th class="u-center">Кол-во</th>
                        <th class="u-right">Цена</th>
                        <th class="u-right">Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td>
                                <?php if ($it['image_url']): ?>
                                    <img src="<?= htmlspecialchars($it['image_url']) ?>" class="product-img" alt="">
                                <?php else: ?>
                                    <div class="thumb-ph"></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="u-white"><?= htmlspecialchars($it['title']) ?></div>
                                <div class="u-muted u-sm"><?= htmlspecialchars($it['artist']) ?></div>
                                <?php if (!empty($it['product_id'])): ?>
                                    <a href="/admin/products/edit.php?id=<?= (int)$it['product_id'] ?>"
                                       class="link-muted u-xs">
                                        К товару →
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="u-center"><?= (int)$it['quantity'] ?></td>
                            <td class="u-right u-muted">
                                <?= number_format($it['price'], 0, '', ' ') ?> ₽
                            </td>
                            <td class="u-right u-strong">
                                <?= number_format($it['subtotal'], 0, '', ' ') ?> ₽
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="u-bt">
                <div class="order-totals-row">
                    <span class="u-muted">Товары</span>
                    <span><?= number_format($order['subtotal'], 0, '', ' ') ?> ₽</span>
                </div>
                <div class="order-totals-row">
                    <span class="u-muted">Доставка</span>
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
    <aside class="u-col">
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

        <?php $ret = $order['return_status'] ?? 'none'; ?>
        <?php if ($ret !== 'none'): ?>
            <div class="status-form">
                <h4>Возврат</h4>

                <div class="status-current">
                    <span class="badge badge-status <?= $return_badge_class[$ret] ?? '' ?>">
                        <?= htmlspecialchars($return_labels[$ret] ?? $ret) ?>
                    </span>
                </div>

                <?php if (!empty($order['return_requested_at'])): ?>
                    <div class="u-muted u-sm" style="margin-bottom:10px;">
                        Запрошен: <?= date('d.m.Y H:i', strtotime($order['return_requested_at'])) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($order['return_reason'])): ?>
                    <div class="note-box" style="margin-bottom:12px;">
                        <?= nl2br(htmlspecialchars($order['return_reason'])) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <select name="return_status">
                        <?php foreach (['requested', 'approved', 'rejected', 'refunded'] as $k): ?>
                            <option value="<?= $k ?>" <?= $ret === $k ? 'selected' : '' ?>>
                                <?= $return_labels[$k] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="update_return" class="btn btn-primary">
                        Сохранить
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="status-form">
            <h4>Контакты покупателя</h4>
            <div style="margin-bottom:10px;">
                <a href="mailto:<?= htmlspecialchars($order['customer_email']) ?>"
                   class="link-accent">
                    <?= htmlspecialchars($order['customer_email']) ?>
                </a>
            </div>
            <div>
                <a href="tel:<?= htmlspecialchars(preg_replace('/\s/', '', $order['customer_phone'])) ?>"
                   class="link-accent">
                    <?= htmlspecialchars($order['customer_phone']) ?>
                </a>
            </div>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
