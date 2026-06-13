-- Поля для возвратов. Запускать один раз.
ALTER TABLE orders
  ADD COLUMN return_status ENUM('none','requested','approved','rejected','refunded')
      NOT NULL DEFAULT 'none',
  ADD COLUMN return_reason TEXT NULL,
  ADD COLUMN return_requested_at DATETIME NULL;
