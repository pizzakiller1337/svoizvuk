<?php
require_once '../includes/auth.php';
$link = get_db();
requireAdmin();

$page_title = 'Добавить пластинку';
$current_page = 'add_product';

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
    $image_url      = trim($_POST['image_url'] ?? '');

    if (empty($title) || empty($artist) || $price <= 0) {
        $error = 'Пожалуйста, заполните обязательные поля: название, исполнитель, цена.';
    } else {
        // Загрузка файла (если передан)
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
            "INSERT INTO products (title, artist, price, year, label, format, catalog_number, description, image_url)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssiisssss", $title, $artist, $price, $year, $label, $format, $catalog_number, $description, $image_url);

        if (mysqli_stmt_execute($stmt)) {
            $new_id = mysqli_insert_id($link);
            $success = "Пластинка добавлена! <a href='../../product.php?id=$new_id' target='_blank' style='color:#4ade80'>Посмотреть на сайте →</a>";
        } else {
            $error = 'Ошибка при добавлении: ' . mysqli_error($link);
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
        <h3>Новая пластинка</h3>
        <a href="index.php" class="btn btn-edit btn-sm">← Назад к списку</a>
    </div>
    <div style="padding: 24px;">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">

                <div class="form-group">
                    <label>Название альбома <span style="color:#f87171">*</span></label>
                    <input type="text" name="title" required value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Исполнитель <span style="color:#f87171">*</span></label>
                    <input type="text" name="artist" required value="<?= isset($_POST['artist']) ? htmlspecialchars($_POST['artist']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Цена (₽) <span style="color:#f87171">*</span></label>
                    <input type="number" name="price" min="0" required value="<?= isset($_POST['price']) ? (int)$_POST['price'] : '' ?>">
                </div>

                <div class="form-group">
                    <label>Год выпуска</label>
                    <input type="number" name="year" min="1900" max="2100" value="<?= isset($_POST['year']) ? (int)$_POST['year'] : '' ?>">
                </div>

                <div class="form-group">
                    <label>Лейбл</label>
                    <input type="text" name="label" value="<?= isset($_POST['label']) ? htmlspecialchars($_POST['label']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Формат</label>
                    <select name="format">
                        <option value="">— Выбрать —</option>
                        <option value="LP" <?= (($_POST['format'] ?? '') === 'LP') ? 'selected' : '' ?>>LP (33 об/мин)</option>
                        <option value="2xLP" <?= (($_POST['format'] ?? '') === '2xLP') ? 'selected' : '' ?>>2×LP</option>
                        <option value="EP" <?= (($_POST['format'] ?? '') === 'EP') ? 'selected' : '' ?>>EP</option>
                        <option value='7"' <?= (($_POST['format'] ?? '') === '7"') ? 'selected' : '' ?>>7"</option>
                        <option value='10"' <?= (($_POST['format'] ?? '') === '10"') ? 'selected' : '' ?>>10"</option>
                        <option value='12"' <?= (($_POST['format'] ?? '') === '12"') ? 'selected' : '' ?>>12"</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Каталожный номер</label>
                    <input type="text" name="catalog_number" value="<?= isset($_POST['catalog_number']) ? htmlspecialchars($_POST['catalog_number']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>URL обложки (или загрузите файл ниже)</label>
                    <input type="url" name="image_url" placeholder="https://..." value="<?= isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Загрузить обложку (файл)</label>
                    <input type="file" name="image" accept="image/*" style="color:#aaa;">
                    <div class="form-hint">Если указан и URL и файл — будет использован файл</div>
                </div>

                <div class="form-group full">
                    <label>Описание</label>
                    <textarea name="description" rows="5"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                </div>

            </div>

            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="index.php" class="btn btn-edit">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>