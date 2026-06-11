<?php
require_once __DIR__ . '/img_helpers.php';
session_start();

require 'db.php';
$link = get_db();

$product_id = (int)($_GET['id'] ?? 0);

if ($product_id <= 0) {
    http_response_code(404);
    die("Товар не найден.");
}

$stmt_product = mysqli_prepare($link, "SELECT * FROM products WHERE product_id = ?");
mysqli_stmt_bind_param($stmt_product, "i", $product_id);
mysqli_stmt_execute($stmt_product);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_product));
if (!$product) {
    http_response_code(404);
    die("Товар не найден.");
}

$stmt_tracks = mysqli_prepare($link, "SELECT title, audio_url FROM tracklist WHERE product_id = ? ORDER BY track_number ASC");
mysqli_stmt_bind_param($stmt_tracks, "i", $product_id);
mysqli_stmt_execute($stmt_tracks);
$tracks = mysqli_fetch_all(mysqli_stmt_get_result($stmt_tracks), MYSQLI_ASSOC);

// OG-превью: название пластинки + обложка
$og_title       = $product['title'] . ' — ' . $product['artist'];
$og_description = 'Послушайте пластинку до покупки в «Свой звук». ' . (int)$product['price'] . ' ₽, доставка по России.';
$og_image       = 'https://www.svoizvuk.online/' . ltrim($product['image_url'], '/');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['title']) ?> — Свой звук</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/assets/logo/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/logo/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/logo/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#171717">
    <?php require __DIR__ . "/meta_og.php"; ?>
    <link rel="stylesheet" href="styles.css?v=12">
</head>
<body>
<?php require 'header.php'; ?>
<main>
    <section class="product-detail_page2">
        <a href="index.php" class="back-link">← Назад в каталог</a>

        <div class="detail-layout">
            <!-- ЛЕВАЯ КОЛОНКА: обложка + треклист -->
            <div class="detail-left">
                <div class="album-cover">
                    <picture>
                        <?= webp_source($product['image_url']) ?>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>"
                             alt="<?= htmlspecialchars($product['title']) ?>" class="cover-img">
                    </picture>
                </div>

                <div class="tracklist">
                    <h3>Треклист</h3>
                    <ol class="tracks-container">
                        <?php if (!empty($tracks)): ?>
                            <?php foreach ($tracks as $index => $track): ?>
                                <li class="track-item" data-track-index="<?= $index ?>">
                                    <span class="track-title"><?= htmlspecialchars($track['title']) ?></span>
                                    <?php if (!empty($track['audio_url'])): ?>
                                        <div class="audio-player" data-src="<?= htmlspecialchars($track['audio_url']) ?>">
                                            <button class="play-btn" title="Воспроизвести" aria-label="Воспроизвести"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M240,128a15.74,15.74,0,0,1-7.6,13.51L88.32,229.65a16,16,0,0,1-16.2.3A15.86,15.86,0,0,1,64,216.13V39.87a15.86,15.86,0,0,1,8.12-13.82,16,16,0,0,1,16.2.3L232.4,114.49A15.74,15.74,0,0,1,240,128Z"/></svg></button>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="no-tracks">Треки не указаны</li>
                        <?php endif; ?>
                    </ol>
                </div>
            </div>

            <!-- ПРАВАЯ КОЛОНКА: вся информация -->
            <div class="detail-right">
                <div class="album-info">
                    <p class="album-eyebrow"><?= htmlspecialchars($product['format']) ?> · <?= (int)$product['year'] ?></p>
                    <h2><?= htmlspecialchars($product['title']) ?></h2>
                    <p class="artist"><?= htmlspecialchars($product['artist']) ?></p>

                    <div class="price-block">
                        <span class="price-label">Цена</span>
                        <span class="price-value"><?= number_format($product['price'], 0, '', ' ') ?> ₽</span>
                    </div>

                    <form method="post" action="add_to_cart.php" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <button type="submit" class="btn btn-primary btn-buy" name="add_to_cart">
                            <span>В корзину</span>
                        </button>
                    </form>

                    <div class="info-grid">
                        <div class="info-cell">
                            <span class="info-label">Год выпуска</span>
                            <span class="info-value"><?= (int)$product['year'] ?></span>
                        </div>
                        <div class="info-cell">
                            <span class="info-label">Лейбл</span>
                            <span class="info-value"><?= htmlspecialchars($product['label']) ?></span>
                        </div>
                        <div class="info-cell">
                            <span class="info-label">Формат</span>
                            <span class="info-value"><?= htmlspecialchars($product['format']) ?></span>
                        </div>
                        <div class="info-cell">
                            <span class="info-label">Каталожный номер</span>
                            <span class="info-value"><?= htmlspecialchars($product['catalog_number'] ?? 'Не указан') ?></span>
                        </div>
                    </div>

                    <?php if (!empty($product['description'])): ?>
                    <div class="description">
                        <h3>Описание</h3>
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/footer.php'; ?>

<script>
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.play-btn');
    if (!btn) return;

    const playerDiv = btn.closest('.audio-player');
    if (!playerDiv || playerDiv.dataset.loaded) return;

    const src = playerDiv.dataset.src;
    if (!src) return;

    const audio = document.createElement('audio');
    audio.controls = true;
    audio.className = 'track-audio';
    audio.src = src;

    playerDiv.innerHTML = '';
    playerDiv.appendChild(audio);
    playerDiv.dataset.loaded = 'true';

    audio.play().catch(() => {});
}, false);

// Винил за обложкой: крутится, пока играет любой трек.
// Заодно ставим на паузу остальные треки при старте нового.
const albumCover = document.querySelector('.album-cover');

document.addEventListener('play', function(e) {
    if (!e.target.matches('audio.track-audio')) return;
    document.querySelectorAll('audio.track-audio').forEach(a => {
        if (a !== e.target) a.pause();
    });
    albumCover && albumCover.classList.add('is-playing');
}, true);

function stopSpinIfSilent() {
    const playing = [...document.querySelectorAll('audio.track-audio')]
        .some(a => !a.paused && !a.ended);
    if (!playing) albumCover && albumCover.classList.remove('is-playing');
}
document.addEventListener('pause', stopSpinIfSilent, true);
document.addEventListener('ended', stopSpinIfSilent, true);
</script>
</body>
</html>