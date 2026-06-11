<?php
require_once __DIR__ . '/../includes/auth.php';
$link = get_db();
requireAdmin();

$page_title   = 'Пластинки';
$current_page = 'products';

$search   = trim($_GET['q'] ?? '');
$per_page = 20;
$page_num = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page_num - 1) * $per_page;

// Условие поиска через prepared statement (а не через конкатенацию строк)
$where_sql = '';
$params    = [];
$types     = '';
if ($search !== '') {
    $where_sql = 'WHERE title LIKE ? OR artist LIKE ?';
    $like      = '%' . $search . '%';
    $params    = [$like, $like];
    $types     = 'ss';
}

// Подсчёт всего
$count_stmt = mysqli_prepare($link, "SELECT COUNT(*) AS cnt FROM products $where_sql");
if ($params) mysqli_stmt_bind_param($count_stmt, $types, ...$params);
mysqli_stmt_execute($count_stmt);
$total_rows  = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['cnt'];
$total_pages = max(1, (int)ceil($total_rows / $per_page));

// Сами товары
$list_sql    = "SELECT * FROM products $where_sql ORDER BY product_id DESC LIMIT ? OFFSET ?";
$list_stmt   = mysqli_prepare($link, $list_sql);
$list_params = $params;
$list_params[] = $per_page;
$list_params[] = $offset;
$list_types  = $types . 'ii';
mysqli_stmt_bind_param($list_stmt, $list_types, ...$list_params);
mysqli_stmt_execute($list_stmt);
$products = mysqli_fetch_all(mysqli_stmt_get_result($list_stmt), MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card-header card-header-plain">
    <div></div>
    <a href="add.php" class="btn btn-primary">Добавить пластинку</a>
</div>

<form method="GET" class="search-bar">
    <input type="text" name="q" placeholder="Поиск по названию или исполнителю..."
           value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-edit">Найти</button>
    <?php if ($search): ?>
        <a href="index.php" class="btn btn-edit">✕ Сбросить</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="card-header">
        <h3>Все пластинки <span class="badge badge-gray"><?= $total_rows ?></span></h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Обложка</th>
                <th>Название</th>
                <th>Исполнитель</th>
                <th>Год</th>
                <th>Цена</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="7" class="empty-state">Ничего не найдено</td></tr>
            <?php endif; ?>

            <?php foreach ($products as $p): ?>
            <tr>
                <td class="u-muted"><?= (int)$p['product_id'] ?></td>
                <td>
                    <?php if ($p['image_url']): ?>
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" class="product-img" alt="">
                    <?php else: ?>
                        <div class="thumb-ph"></div>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="../../product.php?id=<?= (int)$p['product_id'] ?>" target="_blank"
                       class="link-plain" title="Открыть на сайте">
                        <?= htmlspecialchars($p['title']) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($p['artist']) ?></td>
                <td><?= (int)$p['year'] ?></td>
                <td><?= number_format($p['price'], 0, '', ' ') ?> ₽</td>
                <td>
                    <div class="btn-actions">
                        <a href="edit.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-edit btn-sm">Ред.</a>
                        <a href="delete.php?id=<?= (int)$p['product_id'] ?>&t=<?= csrf_token() ?>" class="btn btn-delete btn-sm"
                           onclick="return confirm('Удалить «<?= htmlspecialchars(addslashes($p['title'])) ?>»?')">
                            Удалить
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>"
                   class="page-link <?= $i === $page_num ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
