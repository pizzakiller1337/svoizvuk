<?php
/**
 * Проверяет, что таблицы orders и order_items существуют
 * и содержат нужные колонки (актуальная схема).
 *
 * Возвращает массив:
 *   ['ok' => bool, 'reason' => string]
 *
 * 'ok' = true   — схема актуальная, можно использовать модуль заказов
 * 'ok' = false  — таблиц нет или у них устаревшая структура
 */
function ordersSchemaStatus(mysqli $link): array {
    // Список обязательных колонок таблицы orders
    $required = [
        'order_id', 'order_number', 'customer_name', 'customer_email', 'customer_phone',
        'delivery_method', 'delivery_cost', 'payment_method', 'payment_status',
        'subtotal', 'total', 'status', 'created_at',
    ];

    $res = @mysqli_query($link, 'SHOW COLUMNS FROM orders');
    if (!$res) {
        return ['ok' => false, 'reason' => 'no_table'];
    }

    $columns = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $columns[] = $row['Field'];
    }

    $missing = array_diff($required, $columns);
    if ($missing) {
        return [
            'ok'      => false,
            'reason'  => 'old_schema',
            'missing' => array_values($missing),
        ];
    }

    // Заодно проверим наличие order_items
    $res2 = @mysqli_query($link, 'SHOW COLUMNS FROM order_items');
    if (!$res2) {
        return ['ok' => false, 'reason' => 'no_items_table'];
    }

    return ['ok' => true, 'reason' => ''];
}

/**
 * Краткое описание проблемы для отображения админу
 */
function ordersSchemaErrorHtml(array $status): string {
    if ($status['reason'] === 'no_table' || $status['reason'] === 'no_items_table') {
        return 'Таблицы заказов ещё не созданы. Выполните SQL из файла '
             . '<code>/admin/install_orders.sql</code>, чтобы активировать раздел «Заказы».';
    }
    if ($status['reason'] === 'old_schema') {
        $missing = !empty($status['missing'])
            ? ' Не хватает колонок: <code>' . htmlspecialchars(implode(', ', $status['missing'])) . '</code>.'
            : '';
        return 'Таблица <code>orders</code> существует, но имеет устаревшую структуру.' . $missing
             . '<br>Выполните SQL из файла <code>/admin/migrate_orders.sql</code>, '
             . 'чтобы привести таблицу к актуальной схеме (внимание: миграция удалит существующие данные заказов).';
    }
    return '';
}
