<?php
require 'db.php';
$link = get_db();

$q = $_GET['q'] ?? '';
$q = trim($q);

if ($q === '') {
    echo json_encode([]);
    exit;
}

$q_safe = mysqli_real_escape_string($link, $q);

$result = mysqli_query($link, "
    SELECT * FROM products
    WHERE title LIKE '%$q_safe%'
       OR artist LIKE '%$q_safe%'
    ORDER BY product_id DESC
    LIMIT 12
");

$products = mysqli_fetch_all($result, MYSQLI_ASSOC);

// WebP-версия обложки, если сконвертирована
require_once __DIR__ . '/img_helpers.php';
foreach ($products as &$p) {
    $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $p['image_url']);
    $p['image_webp'] = ($webp !== $p['image_url'] && is_file(__DIR__ . '/' . ltrim($webp, '/'))) ? $webp : null;
}
unset($p);

header('Content-Type: application/json');
echo json_encode($products);
