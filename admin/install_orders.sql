-- ============================================================
-- Установка таблиц заказов для «Свой звук»
--
-- Создаёт таблицы `orders` и `order_items` в актуальной схеме,
-- которую ожидает код (payment.php, admin/orders/*.php).
--
-- Использование:
--   mysql -u root svoizvuk < install_orders.sql
-- или вставить содержимое в phpMyAdmin → SQL.
--
-- Безопасно запускать на пустой БД. Если таблицы уже есть со старой
-- структурой — сначала используйте migrate_orders.sql (он их пересоздаёт).
-- ============================================================

CREATE TABLE IF NOT EXISTS `orders` (
    `order_id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_number`         VARCHAR(32)  NOT NULL,
    `user_id`              INT UNSIGNED NULL,

    -- Контакт покупателя
    `customer_name`        VARCHAR(255) NOT NULL,
    `customer_email`       VARCHAR(255) NOT NULL,
    `customer_phone`       VARCHAR(64)  NOT NULL,

    -- Доставка
    `delivery_method`      ENUM('courier','post','pickup') NOT NULL DEFAULT 'courier',
    `delivery_city`        VARCHAR(255) NULL,
    `delivery_zip`         VARCHAR(16)  NULL,
    `delivery_address`     VARCHAR(500) NULL,
    `delivery_cost`        DECIMAL(10,2) NOT NULL DEFAULT 0,

    `comment`              TEXT NULL,

    -- Оплата
    `payment_method`       ENUM('card','sbp','cash') NOT NULL DEFAULT 'card',
    `payment_card_last4`   VARCHAR(4)  NULL,
    `payment_status`       ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',

    -- Суммы
    `subtotal`             DECIMAL(10,2) NOT NULL DEFAULT 0,
    `total`                DECIMAL(10,2) NOT NULL DEFAULT 0,

    -- Статус заказа
    `status`               ENUM('new','processing','shipped','delivered','cancelled')
                           NOT NULL DEFAULT 'new',

    `created_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                           ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`order_id`),
    UNIQUE KEY `uq_order_number` (`order_number`),
    KEY `idx_user`              (`user_id`),
    KEY `idx_status`            (`status`),
    KEY `idx_created`           (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `order_items` (
    `item_id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`    INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED NULL,

    -- Снимок данных товара на момент заказа.
    -- product_id может стать NULL, если товар удалён, но история заказа сохранится.
    `title`       VARCHAR(255)  NOT NULL,
    `artist`      VARCHAR(255)  NOT NULL,
    `image_url`   VARCHAR(500)  NULL,

    `price`       DECIMAL(10,2) NOT NULL,
    `quantity`    INT UNSIGNED  NOT NULL DEFAULT 1,
    `subtotal`    DECIMAL(10,2) NOT NULL,

    PRIMARY KEY (`item_id`),
    KEY `idx_order`   (`order_id`),
    KEY `idx_product` (`product_id`),
    CONSTRAINT `fk_items_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
