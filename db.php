<?php
if (!function_exists('get_db')) {
    function get_db(): mysqli {
        static $connection = null;
        if ($connection instanceof mysqli) return $connection;

        $cfg = require __DIR__ . '/db.config.php';   // не в репозитории
        $connection = @mysqli_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name']);
        if (!$connection) die('Ошибка подключения: ' . mysqli_connect_error());

        mysqli_set_charset($connection, 'utf8mb4');
        return $connection;
    }
}
$link = get_db();