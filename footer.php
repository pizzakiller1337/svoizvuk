<footer class="site-footer">
    <div class="container footer-inner">

        <!-- Колонка с логотипом и слоганом -->
        <div class="footer-col footer-brand">
            <a href="index.php" class="footer-logo">
                <img src="assets/logo/logo.png" alt="Свой звук" class="footer-logo-icon">
                <span class="footer-logo-text">Свой звук</span>
            </a>
            <p class="footer-about">Магазин виниловых пластинок с предпросмотром: включаете трек прямо в карточке и слушаете до покупки. Оригинальные прессы, честные описания состояния, доставка по России.</p>
        </div>

        <!-- Каталог -->
        <div class="footer-col">
            <h4 class="footer-heading">Каталог</h4>
            <ul class="footer-links">
                <li><a href="index.php#featured">Интересный выбор</a></li>
                <li><a href="index.php?genre=new">Новинки</a></li>
                <li><a href="index.php?genre=popular">Популярное</a></li>
                <li><a href="index.php?genre=rock">Рок</a></li>
                <li><a href="index.php?genre=jazz">Джаз</a></li>
                <li><a href="index.php?genre=electronic">Электроника</a></li>
            </ul>
        </div>

        <!-- Магазин -->
        <div class="footer-col">
            <h4 class="footer-heading">Магазин</h4>
            <ul class="footer-links">
                <li><a href="about.php">О магазине</a></li>
                <li><a href="cart.php">Корзина</a></li>
                <li><a href="index.php">Все пластинки</a></li>
                <?php if (empty($_SESSION['user_id'])): ?>
                    <li><a href="login.php">Войти</a></li>
                    <li><a href="register.php">Регистрация</a></li>
                <?php else: ?>
                    <li><a href="logout.php">Выйти</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Контакты -->
        <div class="footer-col footer-contacts">
            <h4 class="footer-heading">Контакты</h4>
            <ul class="footer-links footer-contact-list">
                <li>
                    <span class="footer-contact-label">Телефон</span>
                    <a href="tel:+78001234567">8 (800) 123-45-67</a>
                </li>
                <li>
                    <span class="footer-contact-label">Email</span>
                    <a href="mailto:hello@svoizvuk.ru">hello@svoizvuk.ru</a>
                </li>
                <li>
                    <span class="footer-contact-label">Адрес</span>
                    <span>Москва, ул. Винильная, 33</span>
                </li>
                <li>
                    <span class="footer-contact-label">Часы работы</span>
                    <span>Пн–Вс · 11:00–22:00</span>
                </li>
            </ul>

            <div class="footer-socials">
                <a href="#" class="social-link" aria-label="VK" title="ВКонтакте">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.79 17.5c-5.49 0-8.62-3.76-8.75-10.02h2.75c.09 4.6 2.12 6.55 3.73 6.95V7.48h2.59v3.97c1.59-.17 3.26-1.98 3.82-3.97h2.59c-.43 2.45-2.24 4.26-3.52 5 1.28.6 3.34 2.17 4.13 5.02h-2.85c-.61-1.91-2.13-3.39-4.17-3.59v3.59h-.32Z" fill="currentColor"/>
                    </svg>
                </a>
                <a href="#" class="social-link" aria-label="MAX" title="MAX">
                    <svg viewBox="-30 -30 780 780" xmlns="http://www.w3.org/2000/svg" fill="none">
                        <path fill="currentColor" d="M350.4,9.6C141.8,20.5,4.1,184.1,12.8,390.4c3.8,90.3,40.1,168,48.7,253.7,2.2,22.2-4.2,49.6,21.4,59.3,31.5,11.9,79.8-8.1,106.2-26.4,9-6.1,17.6-13.2,24.2-22,27.3,18.1,53.2,35.6,85.7,43.4,143.1,34.3,299.9-44.2,369.6-170.3C799.6,291.2,622.5-4.6,350.4,9.6h0ZM269.4,504c-11.3,8.8-22.2,20.8-34.7,27.7-18.1,9.7-23.7-.4-30.5-16.4-21.4-50.9-24-137.6-11.5-190.9,16.8-72.5,72.9-136.3,150-143.1,78-6.9,150.4,32.7,183.1,104.2,72.4,159.1-112.9,316.2-256.4,218.6h0Z"/>
                    </svg>
                </a>
                <a href="#" class="social-link" aria-label="Odnoklassniki" title="Одноклассники">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3.2a3.6 3.6 0 1 0 0 7.2 3.6 3.6 0 0 0 0-7.2Zm0 2.2a1.4 1.4 0 1 1 0 2.8 1.4 1.4 0 0 1 0-2.8Zm-5.55 6.42a1.1 1.1 0 0 1 1.5-.4 7.45 7.45 0 0 0 8.1 0 1.1 1.1 0 0 1 1.15 1.87 9.4 9.4 0 0 1-3.34 1.36l3.22 3.22a1.1 1.1 0 1 1-1.56 1.56L12 16.21l-3.52 3.22a1.1 1.1 0 1 1-1.56-1.56l3.22-3.22a9.4 9.4 0 0 1-3.34-1.36 1.1 1.1 0 0 1-.35-1.47Z" fill="currentColor"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Нижняя строка -->
    <div class="container footer-bottom">
        <p class="footer-copy">© <?= date('Y') ?> Свой звук. Все права защищены.</p>
        <p class="footer-meta">
            <a href="#">Политика конфиденциальности</a>
            <span class="footer-meta-sep">·</span>
            <a href="#">Условия доставки</a>
            <span class="footer-meta-sep">·</span>
            <a href="#">Возврат и обмен</a>
        </p>
    </div>
</footer>

<script>
// Меню пользователя на <details>: закрываем по клику вне и по Esc.
(function () {
    var menu = document.querySelector('.user-menu');
    if (!menu) return;
    document.addEventListener('click', function (e) {
        if (menu.open && !menu.contains(e.target)) menu.removeAttribute('open');
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && menu.open) menu.removeAttribute('open');
    });
})();
</script>