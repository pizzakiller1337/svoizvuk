<?php
require_once '../includes/auth.php';
$link = get_db();
requireAdmin();

$product_id = (int)($_GET['id'] ?? 0);
if (!$product_id) {
    header('Location: index.php');
    exit;
}

// Получить товар
$stmt = mysqli_prepare($link, "SELECT * FROM products WHERE product_id = ?");
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header('Location: index.php');
    exit;
}

// Текущие треки
$stmt = mysqli_prepare($link,
    "SELECT track_id, track_number, title, audio_url
     FROM tracklist
     WHERE product_id = ?
     ORDER BY track_number ASC, track_id ASC"
);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$tracks = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Флеш-сообщение от upload_tracks.php / delete_track.php
$tracks_flash = $_SESSION['tracks_flash'] ?? null;
unset($_SESSION['tracks_flash']);

$page_title = 'Редактировать: ' . htmlspecialchars($product['title']);
$current_page = 'products';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title          = trim($_POST['title']);
    $artist         = trim($_POST['artist']);
    $price          = (int)$_POST['price'];
    $year           = (int)$_POST['year'];
    $label          = trim($_POST['label'] ?? '');
    $format         = trim($_POST['format'] ?? '');
    $catalog_number = trim($_POST['catalog_number'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $image_url      = trim($_POST['image_url'] ?? $product['image_url']);

    if (empty($title) || empty($artist) || $price <= 0) {
        $error = 'Заполните обязательные поля: название, исполнитель, цена.';
    } else {
        // Загрузка нового файла
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = realpath(__DIR__ . '/../../assets/images/') . '/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                $image_url = '/assets/images/' . $filename;
            }
        }

        $stmt = mysqli_prepare($link,
            "UPDATE products SET title=?, artist=?, price=?, year=?, label=?, format=?, catalog_number=?, description=?, image_url=?
             WHERE product_id=?"
        );
        mysqli_stmt_bind_param($stmt, "ssiisssssi", $title, $artist, $price, $year, $label, $format, $catalog_number, $description, $image_url, $product_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = 'Изменения сохранены!';
            // Обновляем локальную переменную
            $product = array_merge($product, compact('title','artist','price','year','label','format','catalog_number','description','image_url'));
        } else {
            $error = 'Ошибка при сохранении: ' . mysqli_error($link);
        }
    }
}

require_once '../includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Редактирование товара #<?= $product_id ?></h3>
        <div style="display:flex;gap:10px;">
            <a href="../../product.php?id=<?= $product_id ?>" target="_blank" class="btn btn-edit btn-sm">👁 На сайте</a>
            <a href="index.php" class="btn btn-edit btn-sm">← Список</a>
        </div>
    </div>
    <div style="padding: 24px; display:flex; gap:24px;">

        <!-- Превью обложки -->
        <div style="flex-shrink:0;">
            <?php if ($product['image_url']): ?>
                <img src="<?= htmlspecialchars($product['image_url']) ?>" style="width:160px;height:160px;object-fit:cover;border-radius:10px;border:1px solid #2a2a2a;" alt="">
            <?php else: ?>
                <div style="width:160px;height:160px;background:#2a2a2a;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:3rem;">🎵</div>
            <?php endif; ?>
        </div>

        <form method="POST" enctype="multipart/form-data" style="flex:1;">
            <div class="form-grid">
                <div class="form-group">
                    <label>Название альбома <span style="color:#f87171">*</span></label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($product['title']) ?>">
                </div>
                <div class="form-group">
                    <label>Исполнитель <span style="color:#f87171">*</span></label>
                    <input type="text" name="artist" required value="<?= htmlspecialchars($product['artist']) ?>">
                </div>
                <div class="form-group">
                    <label>Цена (₽) <span style="color:#f87171">*</span></label>
                    <input type="number" name="price" min="0" required value="<?= (int)$product['price'] ?>">
                </div>
                <div class="form-group">
                    <label>Год выпуска</label>
                    <input type="number" name="year" min="1900" max="2100" value="<?= (int)$product['year'] ?>">
                </div>
                <div class="form-group">
                    <label>Лейбл</label>
                    <input type="text" name="label" value="<?= htmlspecialchars($product['label'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Формат</label>
                    <select name="format">
                        <option value="">— Выбрать —</option>
                        <?php foreach (['LP','2xLP','EP','7"','10"','12"'] as $f): ?>
                            <option value="<?= $f ?>" <?= ($product['format'] === $f) ? 'selected' : '' ?>><?= $f ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Каталожный номер</label>
                    <input type="text" name="catalog_number" value="<?= htmlspecialchars($product['catalog_number'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>URL обложки</label>
                    <input type="url" name="image_url" placeholder="https://..." value="<?= htmlspecialchars($product['image_url'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Заменить обложку (файл)</label>
                    <input type="file" name="image" accept="image/*" style="color:#aaa;">
                </div>
                <div class="form-group full">
                    <label>Описание</label>
                    <textarea name="description" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">💾 Сохранить</button>
                <a href="index.php" class="btn btn-edit">Отмена</a>
                <a href="delete.php?id=<?= $product_id ?>&t=<?= csrf_token() ?>" class="btn btn-delete" onclick="return confirm('Удалить этот товар?')" style="margin-left:auto;">🗑 Удалить</a>
            </div>
        </form>
    </div>
</div>

<!-- ============ ТРЕКИ ============ -->
<div class="card" style="margin-top:24px;">
    <div class="card-header">
        <h3>🎵 Треки <span style="color:#666;font-weight:400;font-size:0.9rem;">(<?= count($tracks) ?>)</span></h3>
    </div>

    <?php if ($tracks_flash): ?>
        <div style="padding:0 24px;">
            <div class="alert alert-<?= $tracks_flash['type'] === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($tracks_flash['msg']) ?>
            </div>
        </div>
    <?php endif; ?>

    <div style="padding: 8px 24px 24px;">
        <?php if (empty($tracks)): ?>
            <p style="color:#777;margin: 0 0 20px;">Пока ни одного трека. Загрузи ZIP с MP3 — они автоматически распакуются и привяжутся к этому альбому.</p>
        <?php else: ?>
            <table style="margin-bottom: 24px;">
                <thead>
                    <tr>
                        <th style="width:50px;">№</th>
                        <th>Название</th>
                        <th style="width:280px;">Превью</th>
                        <th style="width:90px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tracks as $t): ?>
                    <tr>
                        <td style="color:#888;font-variant-numeric: tabular-nums;">
                            <?= str_pad((string)$t['track_number'], 2, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td><?= htmlspecialchars($t['title']) ?></td>
                        <td>
                            <?php if (!empty($t['audio_url'])): ?>
                                <audio controls preload="none" src="<?= htmlspecialchars($t['audio_url']) ?>" style="width:100%;height:32px;"></audio>
                            <?php else: ?>
                                <span style="color:#666;font-size:0.85rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="delete_track.php?id=<?= (int)$t['track_id'] ?>&product=<?= $product_id ?>&t=<?= csrf_token() ?>"
                               class="btn btn-delete btn-sm"
                               onclick="return confirm('Удалить трек «<?= htmlspecialchars(addslashes($t['title']), ENT_QUOTES) ?>»?')">
                                Удалить
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Форма загрузки ZIP -->
        <form method="POST" action="upload_tracks.php" enctype="multipart/form-data"
              style="border:1px dashed #333;border-radius:10px;padding:20px;background:#161616;">
            <input type="hidden" name="product_id" value="<?= $product_id ?>">

            <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px;">
                <div class="form-group" style="flex:1;min-width:260px;margin:0;">
                    <label>ZIP-архив с треками</label>
                    <input type="file" name="archive" accept=".zip,application/zip,application/x-zip-compressed" required style="color:#aaa;">
                </div>

                <div class="form-group" style="margin:0;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:400;color:#bbb;">
                        <input type="checkbox" name="replace_existing" value="1" style="width:auto;margin:0;">
                        Удалить старые треки
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">⬆ Загрузить</button>
            </div>

            <p style="color:#666;font-size:0.82rem;margin: 14px 0 0;line-height:1.5;">
                Поддерживаемые форматы: mp3, wav, ogg, m4a, flac, aac.<br>
                Если в имени файла есть числовой префикс (например <code>01 - Alison.mp3</code>) — он будет использован как номер трека.
                Иначе треки нумеруются по алфавитному порядку имён.
            </p>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>