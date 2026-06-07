<?php
require_once '../includes/auth.php';
$link = get_db();
requireAdmin();

// CSRF: GET-удаление возможно только при валидном токене из той же сессии.
// Это защищает от подсунутой картинки/ссылки в духе <img src="...delete.php?id=5">.
if (!csrf_check($_GET['t'] ?? '')) {
    http_response_code(403);
    exit('Bad CSRF token');
}

$product_id = (int)($_GET['id'] ?? 0);

if ($product_id) {
    $stmt = mysqli_prepare($link, "DELETE FROM products WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
}

header('Location: index.php?deleted=1');
exit;