<?php
session_start();

require 'db.php';
$link = get_db();

// Считаем кое-какую статистику для блока «в цифрах»
$stats = [
    'albums' => 0,
    'genres' => 0,
    'years'  => date('Y') - 2018,   // условный год основания
];

$r = mysqli_query($link, "SELECT COUNT(*) AS c FROM products");
if ($r) { $stats['albums'] = (int)mysqli_fetch_assoc($r)['c']; }

$r = mysqli_query($link, "SELECT COUNT(*) AS c FROM categories");
if ($r) { $stats['genres'] = (int)mysqli_fetch_assoc($r)['c']; }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>О магазине — Свой звук</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/assets/logo/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/assets/logo/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/logo/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/logo/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#171717">
    <link rel="stylesheet" href="styles.css?v=7">
</head>
<body>
<?php require 'header.php'; ?>

<main class="about-page">

    <!-- HERO -->
    <section class="about-hero">
        <div class="container">
            <h1 class="about-hero-title">О магазине</h1>
            <p class="about-hero-sub">
                Магазин винила в Москве, с 2018-го. Каталог небольшой и плотный:
                оригинальные пресы, аудиофильские переиздания, релизы независимых
                лейблов. Сами слушаем — поэтому если у пластинки есть нюанс,
                пишем в карточке. До покупки, не после.
            </p>
        </div>
    </section>

    <!-- СТАТИСТИКА В ЦИФРАХ -->
    <section class="about-stats container">
        <div class="stat-card">
            <span class="stat-value"><?= $stats['years'] ?></span>
            <span class="stat-label">лет работаем</span>
        </div>
        <div class="stat-card">
            <span class="stat-value"><?= $stats['albums'] ?></span>
            <span class="stat-label">пластинок на полке</span>
        </div>
        <div class="stat-card">
            <span class="stat-value"><?= $stats['genres'] ?></span>
            <span class="stat-label">жанров</span>
        </div>
    </section>

    <!-- ЧЕМ МЫ ЗАНИМАЕМСЯ -->
    <section class="about-section container">
        <header class="about-section-head">
            <h2 class="about-section-title">Что мы продаём</h2>
        </header>

        <div class="about-grid about-grid--3">
            <article class="about-tile">
                <h3 class="tile-title">Оригиналы и первопрессы</h3>
                <p class="tile-text">
                    Первый прес, лимитированные тиражи, бокс-сеты. Спорные позиции
                    прослушиваем перед продажей — потёртость на конверте часто
                    не значит ничего, а скрытая царапина значит всё.
                </p>
            </article>

            <article class="about-tile">
                <h3 class="tile-title">Аудиофильские переиздания</h3>
                <p class="tile-text">
                    180 г, ремастеры с оригинальных мастер-лент. В каталоге Music On Vinyl,
                    Mobile Fidelity, Analogue Productions, иногда Impex и ORG. Всё, что у нас
                    стоит на полке, мы реально слышали.
                </p>
            </article>

            <article class="about-tile">
                <h3 class="tile-title">Малые лейблы</h3>
                <p class="tile-text">
                    Релизы тиражом 300–500 копий, локальные сцены, имена, которых нет
                    в основных дистрибьюторских списках. Иногда — последний экземпляр
                    на Discogs.
                </p>
            </article>
        </div>
    </section>

    <!-- ПОЧЕМУ МЫ -->
    <section class="about-section container">
        <header class="about-section-head">
            <h2 class="about-section-title">Как у нас устроено</h2>
        </header>

        <div class="about-grid about-grid--3 features-grid">
            <div class="feature-item">
                <h4 class="feature-title">Без неожиданностей</h4>
                <p class="feature-text">Если у пластинки есть нюанс — потёртый угол, не самый чистый прес, лёгкий фон на тишине — пишем в карточке. До покупки.</p>
            </div>
            <div class="feature-item">
                <h4 class="feature-title">Упаковка</h4>
                <p class="feature-text">Двойная коробка, картон между конвертами, маркировка «хрупкое». Если что-то приедет помятым — возмещаем без объяснений.</p>
            </div>
            <div class="feature-item">
                <h4 class="feature-title">Возврат</h4>
                <p class="feature-text">14 дней по любой причине. Условие одно: не вскрыт шринк, если пластинка была запечатана.</p>
            </div>
        </div>
    </section>

    <!-- КОНТАКТЫ + CTA -->
    <section class="about-contact-block">
        <div class="container">
            <div class="contact-inner">
                <div class="contact-left">
                    <h2 class="contact-title">Напишите — поможем выбрать</h2>
                    <p class="contact-sub">
                        Отвечаем сами. Расскажем про конкретный прес, посоветуем,
                        если непонятно, что выбрать из переизданий. Можно просто
                        поговорить о музыке — мы за.
                    </p>
                    <a href="index.php" class="contact-cta">Открыть каталог</a>
                </div>

                <div class="contact-right">
                    <div class="contact-row">
                        <span class="contact-label">MAX</span>
                        <a class="contact-value" href="https://t.me/svoi_zvuk">@svoizvuk</a>
                    </div>
                    <div class="contact-row">
                        <span class="contact-label">Email</span>
                        <a class="contact-value" href="mailto:hello@svoizvuk.ru">hello@svoizvuk.ru</a>
                    </div>
                    <div class="contact-row">
                        <span class="contact-label">Самовывоз</span>
                        <span class="contact-value">Москва, ул. Винильная, 33</span>
                    </div>
                    <div class="contact-row">
                        <span class="contact-label">Часы</span>
                        <span class="contact-value">Пн–Пт 11–20, Сб 12–18</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>