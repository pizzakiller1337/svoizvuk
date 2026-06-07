<?php
session_start();

if (!isset($_POST['add_to_cart'], $_POST['product_id'])) {
    header("Location: index.php");
    exit;
}

$product_id = (int)$_POST['product_id'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]++;
} else {
    $_SESSION['cart'][$product_id] = 1;
}

header("Location: cart.php");
exit;
