<?php
// Параметры для подключения к базе данных
$dsn = "mysql:host=mysql;dbname=form_data;charset=utf8";
$dbUsername = "root";
$dbPassword = "123";

try {
    // Подключение к базе данных через PDO
    $pdo = new PDO($dsn, $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}