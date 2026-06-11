<?php
require_once __DIR__ . '/includes/auth.php';
$link = get_db();
require_once __DIR__ . '/includes/orders_schema.php';
requireAdmin();

$page_title   = 'Дашборд';
$current_page = 'dashboard';

/**
 * Хелпер: одиночное число из COUNT/SUM.
 * Использует try/catch, потому что в современных версиях PHP/mysqli ошибки SQL
 * выбрасываются как mysqli_sql_exception.
 */
function adm_scalar(mysqli $link, string $sql, $default = 0) {
    try {
        $r = mysqli_query($link, $sql);
    } catch (Throwable $e) {
        return $default;
    }
    if (!$r) return $default;
    $row = mysqli_fetch_row($r);
    return $row ? ($row[0] ?? $default) : $default;
}

// Проверяем актуальность схемы заказов
$orders_schema = ordersSchemaStatus($link);

// Базовая статистика
$total_products = (int)adm_scalar($link, "SELECT COUNT(*) FROM products");
$total_users    = (int)adm_scalar($link, "SELECT COUNT(*) FROM users");

$total_orders     = 0;
$new_orders       = 0;
$revenue_total    = 0.0;
$revenue_30_days  = 0.0;
$recent_orders    = [];

if ($orders_schema['ok']) {
    $total_orders    = (int)adm_scalar($link, "SELECT COUNT(*) FROM orders");
    $new_orders      = (int)adm_scalar($link, "SELECT COUNT(*) FROM orders WHERE status = 'new'");
    $revenue_total   = (float)adm_scalar($link, "SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'");
    $revenue_30_days = (float)adm_scalar(
        $link,
        "SELECT COALESCE(SUM(total),0)
         FROM orders
         WHERE status != 'cancelled'
           AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );

    try {
        $res = mysqli_query($link,
            "SELECT order_id, order_number, customer_name, total, status, created_at
             FROM orders
             ORDER BY created_at DESC
             LIMIT 8"
        );
        if ($res) $recent_orders = mysqli_fetch_all($res, MYSQLI_ASSOC);
    } catch (Throwable $e) {
        // оставляем пустой массив
    }
}

// Последние товары
$recent_products = mysqli_query($link, "SELECT * FROM products ORDER BY product_id DESC LIMIT 5");

$status_labels = [
    'new'        => 'Новый',
    'processing' => 'В обработке',
    'shipped'    => 'Отправлен',
    'delivered'  => 'Доставлен',
    'cancelled'  => 'Отменён',
];

require_once __DIR__ . '/includes/header.php';
?>

<?php if (!$orders_schema['ok']): ?>
    <div class="alert alert-error">
        <?= ordersSchemaErrorHtml($orders_schema) ?>
    </div>
<?php endif; ?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="label">Всего заказов</div>
        <div class="value"><?= $total_orders ?></div>
        <?php if ($new_orders > 0): ?>
            <div class="sublabel u-accent">+ <?= $new_orders ?> новых</div>
        <?php endif; ?>
    </div>
    <div class="stat-card">
        <div class="label">Выручка за 30 дней</div>
        <div class="value"><?= number_format($revenue_30_days, 0, '', ' ') ?> ₽</div>
        <div class="sublabel">всего: <?= number_format($revenue_total, 0, '', ' ') ?> ₽</div>
    </div>
    <div class="stat-card">
        <div class="label">Пластинок в каталоге</div>
        <div class="value"><?= $total_products ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Пользователей</div>
        <div class="value"><?= $total_users ?></div>
    </div>
</div>

<?php if ($orders_schema['ok']): ?>
<div class="card u-mb20">
    <div class="card-header">
        <h3>Последние заказы</h3>
        <a href="orders/index.php" class="btn btn-edit btn-sm">Все заказы →</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Номер</th>
                <th>Дата</th>
                <th>Покупатель</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recent_orders)): ?>
                <tr><td colspan="6" class="empty-state">Заказов пока нет</td></tr>
            <?php endif; ?>

            <?php foreach ($recent_orders as $o): ?>
            <tr>
                <td class="u-mono">
                    <?= htmlspecialchars($o['order_number']) ?>
                </td>
                <td class="u-muted u-sm">
                    <?= date('d.m.Y H:i', strtotime($o['created_at'])) ?>
                </td>
                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                <td class="u-strong">
                    <?= number_format($o['total'], 0, '', ' ') ?> ₽
                </td>
                <td>
                    <span class="badge badge-status badge-status-<?= htmlspecialchars($o['status']) ?>">
                        <?= htmlspecialchars($status_labels[$o['status']] ?? $o['status']) ?>
                    </span>
                </td>
                <td>
                    <a href="orders/view.php?id=<?= (int)$o['order_id'] ?>" class="btn btn-edit btn-sm">
                        Открыть
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Последние добавленные пластинки</h3>
        <a href="products/index.php" class="btn btn-edit btn-sm">Все товары →</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Обложка</th>
                <th>Название</th>
                <th>Исполнитель</th>
                <th>Цена</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = mysqli_fetch_assoc($recent_products)): ?>
            <tr>
                <td>
                    <?php if ($p['image_url']): ?>
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" class="product-img" alt="">
                    <?php else: ?>
                        <div class="thumb-ph"></div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= htmlspecialchars($p['artist']) ?></td>
                <td><?= number_format($p['price'], 0, '', ' ') ?> ₽</td>
                <td>
                    <div class="btn-actions">
                        <a href="products/edit.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-edit btn-sm">Ред.</a>
                        <a href="products/delete.php?id=<?= (int)$p['product_id'] ?>&t=<?= csrf_token() ?>"
                           class="btn btn-delete btn-sm"
                           onclick="return confirm('Удалить?')">Удалить</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
