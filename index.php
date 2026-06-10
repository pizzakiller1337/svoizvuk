<?php
session_start();

require 'db.php';
$link = get_db();

$categories_result = mysqli_query($link,
    "SELECT * FROM categories
     ORDER BY
        CASE LOWER(name)
            WHEN 'featured'             THEN 1
            WHEN 'интересный выбор'     THEN 1
            WHEN 'new'                  THEN 2
            WHEN 'новинки'              THEN 2
            WHEN 'popular'              THEN 3
            WHEN 'популярное'           THEN 3
            WHEN 'rock'                 THEN 4
            WHEN 'рок'                  THEN 4
            WHEN 'post-punk'            THEN 5
            WHEN 'shoegaze'             THEN 6
            WHEN 'indie/alternative'    THEN 7
            WHEN 'electronic'           THEN 8
            WHEN 'электроника'          THEN 8
            WHEN 'electro/idm/ambient'  THEN 9
            WHEN 'hip-hop'              THEN 10
            WHEN 'soul/funk'            THEN 11
            WHEN 'jazz'                 THEN 12
            WHEN 'джаз'                 THEN 12
            WHEN 'folk'                 THEN 13
            WHEN 'classical'            THEN 14
            ELSE 15
        END,
        name ASC"
);
$categories = mysqli_fetch_all($categories_result, MYSQLI_ASSOC);

$category_titles = [
    // Английские ключи (как в SQL-скрипте add_genres_and_albums.sql)
    'featured'           => 'Интересный выбор',
    'new'                => 'Новинки',
    'popular'            => 'Популярное',
    'rock'               => 'Рок',
    'post-punk'          => 'Post-Punk / New Wave',
    'shoegaze'           => 'Shoegaze / Dream Pop',
    'indie/alternative'  => 'Инди / Альтернатива',
    'electronic'         => 'Электроника',
    'electro/IDM/Ambient'=> 'Электро / IDM / Ambient',
    'hip-hop'            => 'Hip-Hop',
    'soul/funk'          => 'Soul / Funk',
    'jazz'               => 'Джаз',
    'folk'               => 'Фолк',
    'classical'          => 'Современная классика',

    // Русские ключи (если в БД категории уже названы по-русски)
    'Интересный выбор'   => 'Интересный выбор',
    'Новинки'            => 'Новинки',
    'Популярное'         => 'Популярное',
    'Рок'                => 'Рок',
    'Электроника'        => 'Электроника',
    'Джаз'               => 'Джаз',
];

$selected_genre = isset($_GET['genre']) ? trim($_GET['genre']) : '';

$category_products = [];
foreach ($categories as $category) {
    $category_id = (int)$category['category_id'];
    $cat_name    = $category['name'];

    if ($selected_genre !== '' && $selected_genre !== $cat_name) continue;

    $products_result = mysqli_query($link,
        "SELECT * FROM products
         WHERE category_id = $category_id
         ORDER BY product_id DESC LIMIT 6"
    );
    $category_products[$category_id] = [
        'category' => $category,
        'products' => mysqli_fetch_all($products_result, MYSQLI_ASSOC)
    ];
}

$all_products_result = mysqli_query($link, "SELECT * FROM products ORDER BY product_id DESC");
$all_products = mysqli_fetch_all($all_products_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/assets/logo/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/logo/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/logo/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#171717">
    <title>Свой звук</title>
    <link rel="stylesheet" href="styles.css?v=5">
</head>
<body>

<?php require 'header.php'; ?>

<nav class="category-nav">
    <div class="container">
        <div class="chips-slider">
            <button type="button" class="chips-arrow chips-arrow-left" aria-label="Прокрутить влево">‹</button>
            <div class="chips-viewport">
                <div class="nav-links chips">
                    <a href="index.php" class="chip <?= !$selected_genre ? 'active' : '' ?>">Все</a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="?genre=<?= htmlspecialchars($cat['name']) ?>"
                           class="chip <?= $selected_genre === $cat['name'] ? 'active' : '' ?>">
                            <?= $category_titles[$cat['name']] ?? ucfirst($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="button" class="chips-arrow chips-arrow-right" aria-label="Прокрутить вправо">›</button>
        </div>
        <div class="right-controls">
            <div class="nav-search">
                <input type="text" id="live-search" class="search-input" placeholder="Поиск пластинок" autocomplete="off">
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <div id="search-results" class="products-grid" style="display:none;"></div>
</div>

<div class="main-layout container">

    <div class="catalog-content">
        <main>
            <?php foreach ($category_products as $category_data): ?>
                <?php if (!empty($category_data['products'])): ?>
                    <section class="category-section">
                        <?php
                            $cat_name     = $category_data['category']['name'];
                            $section_title = $category_titles[$cat_name] ?? ucfirst($cat_name);
                            $section_id   = strtolower(str_replace(['/', ' '], '-', $cat_name));
                        ?>
                        <h2 id="<?= $section_id ?>" class="section-title"><?= $section_title ?></h2>
                        <div class="products-grid">
                            <?php foreach ($category_data['products'] as $item): ?>
                                <article class="product-card" data-product-id="<?= (int)$item['product_id'] ?>">
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                    <h3 class="album-title"><?= htmlspecialchars($item['title']) ?></h3>
                                    <p class="artist"><?= htmlspecialchars($item['artist']) ?></p>
                                    <div class="hidden-data" style="display:none;">
                                        <span class="price"><?= number_format($item['price'], 0, '', ' ') ?> ₽</span>
                                        <span class="year"><?= (int)$item['year'] ?></span>
                                        <span class="label"><?= htmlspecialchars($item['label']) ?></span>
                                        <span class="format"><?= htmlspecialchars($item['format']) ?></span>
                                        <span class="details-link">product.php?id=<?= (int)$item['product_id'] ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endforeach; ?>

            <section id="all" class="category-section">
                <h2 class="section-title">Все пластинки</h2>
                <div class="products-grid">
                    <?php if (empty($all_products)): ?>
                        <p class="no-products">Товаров пока нет.</p>
                    <?php else: ?>
                        <?php foreach ($all_products as $item): ?>
                            <article class="product-card" data-product-id="<?= (int)$item['product_id'] ?>">
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                <h3 class="album-title"><?= htmlspecialchars($item['title']) ?></h3>
                                <p class="artist"><?= htmlspecialchars($item['artist']) ?></p>
                                <div class="hidden-data" style="display:none;">
                                    <span class="price"><?= number_format($item['price'], 0, '', ' ') ?> ₽</span>
                                    <span class="year"><?= (int)$item['year'] ?></span>
                                    <span class="label"><?= htmlspecialchars($item['label']) ?></span>
                                    <span class="format"><?= htmlspecialchars($item['format']) ?></span>
                                    <span class="details-link">product.php?id=<?= (int)$item['product_id'] ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <aside class="album-sidebar">
        <div class="sidebar-inner">
            <div id="selected-album" class="album-preview">
                <div class="placeholder">
                    <p>Нажмите на любую пластинку,<br>чтобы увидеть подробности</p>
                    <div class="placeholder-eq" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
    </aside>

</div>

<?php include __DIR__ . '/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('live-search');
    const resultsBox  = document.getElementById('search-results');
    let timer = null;

    if (searchInput && resultsBox) {
        searchInput.addEventListener('input', () => {
            clearTimeout(timer);
            const query = searchInput.value.trim();
            if (query.length < 2) {
                resultsBox.style.display = 'none';
                resultsBox.innerHTML = '';
                return;
            }
            timer = setTimeout(() => {
                fetch('search.php?q=' + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(data => {
                        resultsBox.innerHTML = '';
                        if (data.length === 0) {
                            resultsBox.innerHTML = '<p class="no-products">Ничего не найдено</p>';
                        } else {
                            data.forEach(item => {
                                resultsBox.innerHTML += `
                                    <article class="product-card">
                                        <img src="${item.image_url}" alt="${item.title}">
                                        <h3 class="album-title">${item.title}</h3>
                                        <p class="artist">${item.artist}</p>
                                        <div class="hidden-data" style="display:none;">
                                            <span class="price">${Number(item.price).toLocaleString('ru-RU')} ₽</span>
                                            <span class="year">${item.year}</span>
                                            <span class="label">${item.label}</span>
                                            <span class="format">${item.format}</span>
                                            <span class="details-link">product.php?id=${item.product_id}</span>
                                        </div>
                                    </article>`;
                            });
                        }
                        resultsBox.style.display = 'grid';
                        attachCardClickListeners();
                    });
            }, 300);
        });
    }

    // ----- Слайдер жанров (стрелки + блокировка на краях) -----
    const chipsViewport = document.querySelector('.chips-viewport');
    const arrowLeft     = document.querySelector('.chips-arrow-left');
    const arrowRight    = document.querySelector('.chips-arrow-right');

    if (chipsViewport && arrowLeft && arrowRight) {
        const updateArrows = () => {
            const max = chipsViewport.scrollWidth - chipsViewport.clientWidth;
            // Если контент влезает целиком — прячем стрелки совсем
            if (max <= 2) {
                arrowLeft.style.display = 'none';
                arrowRight.style.display = 'none';
                return;
            }
            arrowLeft.style.display = '';
            arrowRight.style.display = '';
            arrowLeft.disabled  = chipsViewport.scrollLeft <= 2;
            arrowRight.disabled = chipsViewport.scrollLeft >= max - 2;
        };

        const scrollByStep = (dir) => {
            const step = chipsViewport.clientWidth * 0.8;
            chipsViewport.scrollBy({ left: dir * step, behavior: 'smooth' });
        };

        arrowLeft.addEventListener('click',  () => scrollByStep(-1));
        arrowRight.addEventListener('click', () => scrollByStep(1));
        chipsViewport.addEventListener('scroll', updateArrows, { passive: true });
        window.addEventListener('resize', updateArrows);

        // Подскролл к активному чипу, чтобы он сразу был виден
        const activeChip = chipsViewport.querySelector('.chip.active');
        if (activeChip) {
            const chipLeft   = activeChip.offsetLeft;
            const chipRight  = chipLeft + activeChip.offsetWidth;
            const viewLeft   = chipsViewport.scrollLeft;
            const viewRight  = viewLeft + chipsViewport.clientWidth;
            if (chipLeft < viewLeft || chipRight > viewRight) {
                chipsViewport.scrollLeft = chipLeft - 24;
            }
        }

        updateArrows();
    }

    // Раскрытие деталей. На десктопе — в боковой панели.
    // На телефоне (<=600px) — раскрывающаяся панель прямо под рядом
    // нажатой карточки (как в iTunes / App Store).
    const isMobileView = () => window.matchMedia('(max-width: 600px)').matches;

    function buildSidebarHTML(d) {
        return `
            <img src="${d.imgSrc}" alt="${d.title}" class="sidebar-cover">
            <h2>${d.title}</h2>
            <p class="artist-big">${d.artist}</p>
            <p class="price-big">${d.price}</p>
            <div class="details-expanded">
                <p><strong>Год:</strong> ${d.year}</p>
                <p><strong>Лейбл:</strong> ${d.label}</p>
                <p><strong>Формат:</strong> ${d.format}</p>
            </div>
            <a href="${d.detailsLink}" class="full-width">Подробнее / В корзину</a>`;
    }

    function buildInlineHTML(d) {
        return `
            <button type="button" class="inline-detail-close" aria-label="Закрыть">&times;</button>
            <div class="inline-detail-body">
                <img src="${d.imgSrc}" alt="${d.title}" class="inline-detail-cover">
                <div class="inline-detail-info">
                    <h3 class="inline-detail-title">${d.title}</h3>
                    <p class="inline-detail-artist">${d.artist}</p>
                    <p class="inline-detail-price">${d.price}</p>
                    <div class="inline-detail-meta">
                        <span><strong>Год:</strong> ${d.year}</span>
                        <span><strong>Лейбл:</strong> ${d.label}</span>
                        <span><strong>Формат:</strong> ${d.format}</span>
                    </div>
                    <a href="${d.detailsLink}" class="full-width inline-detail-btn">Подробнее / В корзину</a>
                </div>
            </div>`;
    }

    function closeInlineDetail(grid) {
        const panel = grid.querySelector('.inline-detail');
        if (panel) panel.remove();
        grid.querySelectorAll('.product-card.card-active')
            .forEach(c => c.classList.remove('card-active'));
    }

    function showInlineDetail(card, grid, d) {
        const cards = Array.from(grid.querySelectorAll('.product-card'));
        const idx   = cards.indexOf(card);
        if (idx === -1) return;

        // Сколько колонок сейчас в сетке
        const cols = getComputedStyle(grid).gridTemplateColumns.split(' ').filter(Boolean).length || 1;

        const existing = grid.querySelector('.inline-detail');
        // Повторный клик по той же карточке — закрываем (toggle)
        if (existing && existing.dataset.forCard === String(idx)) {
            closeInlineDetail(grid);
            return;
        }
        closeInlineDetail(grid);

        // Последняя карточка в ряду нажатой — после неё вставим панель,
        // чтобы она раскрылась под всем рядом во всю ширину.
        const rowLastIdx = Math.min(Math.floor(idx / cols) * cols + cols - 1, cards.length - 1);
        const anchor = cards[rowLastIdx];

        const panel = document.createElement('div');
        panel.className = 'inline-detail';
        panel.dataset.forCard = String(idx);
        panel.innerHTML = buildInlineHTML(d);

        anchor.after(panel);
        card.classList.add('card-active');

        panel.querySelector('.inline-detail-close')
             ?.addEventListener('click', () => closeInlineDetail(grid));

        // Показать панель целиком в зоне видимости
        requestAnimationFrame(() => {
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }

    function attachCardClickListeners() {
        document.querySelectorAll('.product-card').forEach(card => {
            if (card.dataset.clickBound) return;   // не вешать обработчик дважды
            card.dataset.clickBound = '1';

            card.addEventListener('click', (e) => {
                if (e.target.closest('a')) return;
                e.preventDefault();

                const hidden = card.querySelector('.hidden-data');
                const d = {
                    imgSrc:      card.querySelector('img').src,
                    title:       card.querySelector('.album-title')?.textContent.trim() || '—',
                    artist:      card.querySelector('.artist')?.textContent.trim()      || '—',
                    price:       hidden?.querySelector('.price')?.textContent.trim()    || '—',
                    year:        hidden?.querySelector('.year')?.textContent.trim()     || '—',
                    label:       hidden?.querySelector('.label')?.textContent.trim()    || '—',
                    format:      hidden?.querySelector('.format')?.textContent.trim()   || '—',
                    detailsLink: hidden?.querySelector('.details-link')?.textContent    || '#',
                };

                const grid = card.closest('.products-grid');
                if (isMobileView() && grid) {
                    showInlineDetail(card, grid, d);
                } else {
                    const sidebar = document.getElementById('selected-album');
                    if (sidebar) sidebar.innerHTML = buildSidebarHTML(d);
                }
            });
        });
    }
    attachCardClickListeners();

    // При повороте экрана / ресайзе закрываем инлайн-панель, чтобы
    // не оставалась открытой при переключении мобильный <-> десктоп.
    window.addEventListener('resize', () => {
        document.querySelectorAll('.products-grid').forEach(g => {
            if (!isMobileView()) closeInlineDetail(g);
        });
    });
});
</script>
</body>
</html>