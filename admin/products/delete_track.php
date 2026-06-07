<?php
/**
 * Удаление одного трека из tracklist.
 * Принимает GET ?id=<track_id>&product=<product_id>
 * Удаляет файл с диска (если лежит внутри /assets/audio) и запись из БД.
 */

require_once '../includes/auth.php';
$link = get_db();
requireAdmin();

// CSRF: защита GET-удаления.
if (!csrf_check($_GET['t'] ?? '')) {
    http_response_code(403);
    exit('Bad CSRF token');
}

$track_id   = (int)($_GET['id'] ?? 0);
$product_id = (int)($_GET['product'] ?? 0);

if (!$track_id || !$product_id) {
    header('Location: index.php');
    exit;
}

// Получаем audio_url для удаления файла
$stmt = mysqli_prepare($link, "SELECT audio_url FROM tracklist WHERE track_id = ? AND product_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $track_id, $product_id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($row) {
    $audio_root = realpath(__DIR__ . '/../../assets/audio');
    $url = ltrim($row['audio_url'] ?? '', '/');
    if ($url && $audio_root) {
        $abs = realpath(__DIR__ . '/../../' . $url);
        // Гарантируем, что файл лежит внутри /assets/audio — защита от path traversal
        if ($abs && is_file($abs) && strpos($abs, $audio_root) === 0) {
            @unlink($abs);
        }
    }

    $stmt = mysqli_prepare($link, "DELETE FROM tracklist WHERE track_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $track_id, $product_id);
    mysqli_stmt_execute($stmt);

    $_SESSION['tracks_flash'] = ['type' => 'success', 'msg' => 'Трек удалён.'];
} else {
    $_SESSION['tracks_flash'] = ['type' => 'error', 'msg' => 'Трек не найден.'];
}

header('Location: edit.php?id=' . $product_id);
exit;
