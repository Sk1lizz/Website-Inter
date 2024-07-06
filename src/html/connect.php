<?php
$host = 'vh303';
$user = '_pma_463660413@localhost';
$password = 'M37GWfbXgv';
$dbname = 'skvantergm';

$mysqli = new mysqli($host, $user, $password, $dbname);

if ($mysqli->connect_error) {
    die('Ошибка подключения (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
?>