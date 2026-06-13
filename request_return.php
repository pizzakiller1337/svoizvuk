<?php
/**
 * Запрос возврата от пользователя. Принимает POST (order_id, reason),
 * проверяет принадлежность заказа и право на возврат, выставляет
 * return_status = 'requested'. Решение принимает админ.
 */
session_start();
require __DIR__ . '/db.php';
$link = get_db();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$uid = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit;
}

$order_id = (int) ($_POST['order_id'] ?? 0);
$reason   = trim($_POST['reason'] ?? '');
$anchor   = '#order-' . $order_id;

$stmt = mysqli_prepare($link, "SELECT status, return_status FROM orders WHERE order_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $order_id, $uid);
mysqli_stmt_execute($stmt);
$o = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$o) {
    header('Location: orders.php?return=notfound');
    exit;
}
if (($o['return_status'] ?? 'none') !== 'none') {
    header('Location: orders.php?return=exists' . $anchor);
    exit;
}
if ($o['status'] === 'cancelled') {
    header('Location: orders.php?return=ineligible' . $anchor);
    exit;
}
if (mb_strlen($reason) < 5) {
    header('Location: orders.php?return=noreason' . $anchor);
    exit;
}

$stmt = mysqli_prepare(
    $link,
    "UPDATE orders
        SET return_status = 'requested', return_reason = ?, return_requested_at = NOW()
      WHERE order_id = ? AND user_id = ? AND return_status = 'none'"
);
mysqli_stmt_bind_param($stmt, 'sii', $reason, $order_id, $uid);
mysqli_stmt_execute($stmt);

header('Location: orders.php?return=ok' . $anchor);
exit;
