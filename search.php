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

header('Content-Type: application/json');
echo json_encode($products);
