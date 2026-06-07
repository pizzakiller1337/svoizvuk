<?php
/**
 * Загрузка треков из ZIP-архива.
 *
 * Принимает POST с product_id и файлом archive.
 * Распаковывает архив, отбирает аудиофайлы (mp3/wav/ogg/m4a/flac),
 * раскладывает их в /assets/audio/{product_id}/, и записывает
 * в таблицу tracklist (title берётся из имени файла без расширения,
 * track_number — из числового префикса в имени, иначе по порядку).
 *
 * После обработки делает редирект обратно на edit.php c флешем-сообщением.
 */

require_once '../includes/auth.php';
$link = get_db();
requireAdmin();

$product_id = (int)($_POST['product_id'] ?? 0);
$replace    = !empty($_POST['replace_existing']);   // галка «удалить старые треки»

$flash = function(string $type, string $msg) use ($product_id) {
    $_SESSION['tracks_flash'] = ['type' => $type, 'msg' => $msg];
    header('Location: edit.php?id=' . $product_id);
    exit;
};

if (!$product_id) {
    $flash('error', 'Не указан товар.');
}

// Проверки файла
if (!isset($_FILES['archive']) || $_FILES['archive']['error'] !== UPLOAD_ERR_OK) {
    $code = $_FILES['archive']['error'] ?? 'нет файла';
    $flash('error', 'Файл не загружен (код ошибки: ' . $code . ').');
}

if (!class_exists('ZipArchive')) {
    $flash('error', 'PHP-расширение zip не установлено на сервере. Попроси хостера включить ext-zip.');
}

$archive_path = $_FILES['archive']['tmp_name'];
$archive_name = $_FILES['archive']['name'];

if (!preg_match('/\.zip$/i', $archive_name)) {
    $flash('error', 'Нужен .zip-архив.');
}

// Открываем архив
$zip = new ZipArchive();
$open_status = $zip->open($archive_path);
if ($open_status !== true) {
    $flash('error', 'Не удалось открыть архив (код ' . $open_status . '). Возможно, файл повреждён.');
}

// Целевая директория /assets/audio/{product_id}/
$base_dir   = realpath(__DIR__ . '/../../assets');
if ($base_dir === false) {
    $base_dir = __DIR__ . '/../../assets';
}
$audio_root = $base_dir . '/audio';
$target_dir = $audio_root . '/' . $product_id;

if (!is_dir($target_dir) && !mkdir($target_dir, 0775, true) && !is_dir($target_dir)) {
    $zip->close();
    $flash('error', 'Не удалось создать папку для треков: ' . $target_dir);
}

// Удаляем старые треки, если попросили
if ($replace) {
    $stmt = mysqli_prepare($link, "SELECT audio_url FROM tracklist WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $old_tracks = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    foreach ($old_tracks as $ot) {
        $url = ltrim($ot['audio_url'] ?? '', '/');
        if ($url) {
            $abs = realpath(__DIR__ . '/../../' . $url);
            if ($abs && is_file($abs) && strpos($abs, $audio_root) === 0) {
                @unlink($abs);
            }
        }
    }
    $stmt = mysqli_prepare($link, "DELETE FROM tracklist WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
}

// Текущий максимальный track_number — чтобы продолжать нумерацию, если не replace
$current_max = 0;
$rs = mysqli_query($link, "SELECT COALESCE(MAX(track_number),0) FROM tracklist WHERE product_id = " . $product_id);
if ($rs) {
    $row = mysqli_fetch_row($rs);
    $current_max = (int)($row[0] ?? 0);
}

// Допустимые форматы
$allowed_ext = ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac'];

// Сначала собираем список аудио-файлов из архива (с метаданными для сортировки)
$entries = [];
for ($i = 0; $i < $zip->numFiles; $i++) {
    $entry_name = $zip->getNameIndex($i);
    if ($entry_name === false) continue;

    // Пропускаем директории, скрытые файлы macOS, прочий мусор
    if (substr($entry_name, -1) === '/') continue;
    $basename = basename($entry_name);
    if ($basename === '' || $basename[0] === '.') continue;          // .DS_Store и т.п.
    if (strpos($entry_name, '__MACOSX/') === 0) continue;

    $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext, true)) continue;

    $entries[] = ['idx' => $i, 'name' => $entry_name, 'base' => $basename, 'ext' => $ext];
}

if (empty($entries)) {
    $zip->close();
    $flash('error', 'В архиве не найдено аудио-файлов (поддерживаются: ' . implode(', ', $allowed_ext) . ').');
}

// Сортируем по имени — стандартный порядок треков на альбоме обычно есть в названии
usort($entries, fn($a, $b) => strnatcasecmp($a['base'], $b['base']));

// Подготовка INSERT
$ins = mysqli_prepare($link,
    "INSERT INTO tracklist (product_id, track_number, title, audio_url) VALUES (?, ?, ?, ?)"
);

$saved = 0;
$skipped = 0;
$track_no = $current_max;

foreach ($entries as $e) {
    $track_no++;
    $name_no_ext = pathinfo($e['base'], PATHINFO_FILENAME);

    // Если в имени есть числовой префикс (например, "01 - Alison", "03_Souvlaki_Space"),
    // используем его как track_number — но только если он адекватный.
    $detected_no = null;
    if (preg_match('/^\s*0*(\d{1,3})[\s._\-]+(.+)$/u', $name_no_ext, $m)) {
        $detected_no = (int)$m[1];
        if ($detected_no >= 1 && $detected_no <= 999) {
            $name_no_ext = trim($m[2]);
        } else {
            $detected_no = null;
        }
    }

    $title_clean = trim($name_no_ext);
    if ($title_clean === '') $title_clean = 'Без названия ' . $track_no;

    $effective_no = $detected_no ?? $track_no;

    // Извлекаем файл с безопасным именем
    $safe_basename = sprintf('%03d_%s.%s',
        $effective_no,
        preg_replace('/[^a-zA-Z0-9_\-]/', '_', transliterate_filename($title_clean)),
        $e['ext']
    );
    $dest_path = $target_dir . '/' . $safe_basename;

    // Если файл с таким именем уже есть — добавим уникальный суффикс
    if (is_file($dest_path)) {
        $safe_basename = sprintf('%03d_%s_%s.%s',
            $effective_no,
            preg_replace('/[^a-zA-Z0-9_\-]/', '_', transliterate_filename($title_clean)),
            uniqid(),
            $e['ext']
        );
        $dest_path = $target_dir . '/' . $safe_basename;
    }

    $stream = $zip->getStream($e['name']);
    if (!$stream) { $skipped++; continue; }

    $out = fopen($dest_path, 'wb');
    if (!$out) { fclose($stream); $skipped++; continue; }
    stream_copy_to_stream($stream, $out);
    fclose($out);
    fclose($stream);

    // Защита: убеждаемся, что итоговый путь не вышел за пределы audio_root
    $real = realpath($dest_path);
    if (!$real || strpos($real, realpath($audio_root)) !== 0) {
        @unlink($dest_path);
        $skipped++;
        continue;
    }

    $audio_url = '/assets/audio/' . $product_id . '/' . $safe_basename;

    mysqli_stmt_bind_param($ins, "iiss", $product_id, $effective_no, $title_clean, $audio_url);
    if (mysqli_stmt_execute($ins)) {
        $saved++;
    } else {
        @unlink($dest_path);
        $skipped++;
    }
}

$zip->close();

$msg = "Загружено треков: $saved";
if ($skipped > 0) $msg .= ", пропущено: $skipped";
$flash($saved > 0 ? 'success' : 'error', $msg);


/**
 * Грубая транслитерация для имени файла. Не идеальная, но достаточная,
 * чтобы итоговое имя файла было безопасным и человекочитаемым.
 */
function transliterate_filename(string $s): string {
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo',
        'ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m',
        'н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
        'ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch',
        'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'Yo',
        'Ж'=>'Zh','З'=>'Z','И'=>'I','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M',
        'Н'=>'N','О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U',
        'Ф'=>'F','Х'=>'H','Ц'=>'Ts','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sch',
        'Ъ'=>'','Ы'=>'Y','Ь'=>'','Э'=>'E','Ю'=>'Yu','Я'=>'Ya',
        ' '=>'_',
    ];
    return strtr($s, $map);
}
