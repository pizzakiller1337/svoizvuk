-- ============================================================
-- Миграция таблиц заказов «Свой звук» на актуальную схему
--
-- ВНИМАНИЕ: этот скрипт удаляет существующие таблицы `orders`
-- и `order_items` со всем содержимым и создаёт их заново.
-- Если в них есть нужные данные — сначала сделайте бэкап:
--
--   mysqldump -u root svoizvuk orders order_items > orders_backup.sql
--
-- После бэкапа запустите:
--   mysql -u root svoizvuk < migrate_orders.sql
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;

SET FOREIGN_KEY_CHECKS = 1;

-- Создаём заново через install_orders.sql
SOURCE install_orders.sql;
