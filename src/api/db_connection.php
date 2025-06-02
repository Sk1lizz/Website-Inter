<?php
$host = 'localhost';
$user = 'skvantergm_fcint';
$pass = '5688132zZ-';
$dbname = 'skvantergm_fcint';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Ошибка подключения к БД']));
}

$conn->set_charset("utf8");
?>