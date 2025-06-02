<?php
$db = new mysqli('localhost', 'skvantergm_fcint', '5688132zZ-', 'skvantergm_fcint');

if ($db->connect_error) {
    die('Ошибка подключения к БД: ' . $db->connect_error);
}

$db->set_charset('utf8mb4');