<?php
require_once __DIR__ . '/../includes/auth.php';
$link = get_db();
require_once __DIR__ . '/../includes/orders_schema.php';
requireAdmin();

$page_title   = 'Заказы';
$current_page = 'orders';

// Проверяем схему — если не в порядке, показываем сообщение и выходим
$orders_schema = ordersSchemaStatus($link);
if (!$orders_schema['ok']) {
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="alert alert-error">' . ordersSchemaErrorHtml($orders_schema) . '</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Фильтры
$search       = trim($_GET['q']      ?? '');
$status       = trim($_GET['status'] ?? '');
$valid_status = ['new','processing','shipped','delivered','cancelled'];

$per_page = 20;
$page_num = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page_num - 1) * $per_page;

// Условия + параметры (prepared statement для безопасности)
$conditions = [];
$params     = [];
$types      = '';

if ($search !== '') {
    $conditions[] = '(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.customer_phone LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like);
    $types .= 'ssss';
}
if (in_array($status, $valid_status, true)) {
    $conditions[] = 'o.status = ?';
    $params[] = $status;
    $types   .= 's';
}

$where_sql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Подсчёт всего записей
$count_sql = "SELECT COUNT(*) AS cnt FROM orders o $where_sql";
$count_stmt = mysqli_prepare($link, $count_sql);
if ($params) mysqli_stmt_bind_param($count_stmt, $types, ...$params);
mysqli_stmt_execute($count_stmt);
$total_rows  = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['cnt'];
$total_pages = max(1, (int)ceil($total_rows / $per_page));

// Сами записи
$list_sql = "SELECT o.*, (SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.order_id) AS items_count
             FROM orders o $where_sql
             ORDER BY o.created_at DESC
             LIMIT ? OFFSET ?";
$list_stmt = mysqli_prepare($link, $list_sql);

$list_params = $params;
$list_types  = $types . 'ii';
$list_params[] = $per_page;
$list_params[] = $offset;
mysqli_stmt_bind_param($list_stmt, $list_types, ...$list_params);
mysqli_stmt_execute($list_stmt);
$orders = mysqli_fetch_all(mysqli_stmt_get_result($list_stmt), MYSQLI_ASSOC);

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

require_once __DIR__ . '/../includes/header.php';
?>

<form method="GET" class="search-bar">
    <input type="text" name="q" placeholder="Поиск по номеру, имени, email, телефону..."
           value="<?= htmlspecialchars($search) ?>">
    <select name="status">
        <option value="">Все статусы</option>
        <?php foreach ($status_labels as $k => $v): ?>
            <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-edit">Найти</button>
    <?php if ($search || $status): ?>
        <a href="index.php" class="btn btn-edit"><?= admin_icon('x', 14) ?> Сбросить</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="card-header">
        <h3>Все заказы <span class="badge badge-gray"><?= $total_rows ?></span></h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Номер</th>
                <th>Дата</th>
                <th>Покупатель</th>
                <th>Товаров</th>
                <th>Сумма</th>
                <th>Оплата</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="8" class="empty-state">Заказов пока нет</td></tr>
            <?php endif; ?>

            <?php foreach ($orders as $o): ?>
            <tr>
                <td>
                    <a href="view.php?id=<?= (int)$o['order_id'] ?>"
                       class="link-plain u-white u-mono">
                        <?= htmlspecialchars($o['order_number']) ?>
                    </a>
                </td>
                <td class="u-muted u-sm">
                    <?= date('d.m.Y', strtotime($o['created_at'])) ?>
                    <div class="u-muted u-xs">
                        <?= date('H:i', strtotime($o['created_at'])) ?>
                    </div>
                </td>
                <td>
                    <div><?= htmlspecialchars($o['customer_name']) ?></div>
                    <div class="u-muted u-sm"><?= htmlspecialchars($o['customer_email']) ?></div>
                </td>
                <td><?= (int)$o['items_count'] ?></td>
                <td class="u-strong"><?= number_format($o['total'], 0, '', ' ') ?> ₽</td>
                <td>
                    <span class="badge badge-pay-<?= htmlspecialchars($o['payment_status']) ?>">
                        <?= htmlspecialchars($payment_labels[$o['payment_status']] ?? $o['payment_status']) ?>
                    </span>
                </td>
                <td>
                    <span class="badge badge-status badge-status-<?= htmlspecialchars($o['status']) ?>">
                        <?= htmlspecialchars($status_labels[$o['status']] ?? $o['status']) ?>
                    </span>
                    <?php if (($o['return_status'] ?? 'none') === 'requested'): ?>
                        <div style="margin-top:6px;">
                            <span class="badge badge-status-cancelled">↩ возврат</span>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="view.php?id=<?= (int)$o['order_id'] ?>" class="btn btn-edit btn-sm">Подробнее</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
                $base_qs = http_build_query(array_filter([
                    'q'      => $search,
                    'status' => $status,
                ]));
                $base_qs = $base_qs ? '&' . $base_qs : '';
            ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?><?= $base_qs ?>"
                   class="page-link <?= $i === $page_num ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
