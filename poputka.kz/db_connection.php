<?php
$servername = "localhost";
$username = "poputka_kz";
$password = "plAEQeJRt77b2Da1";
$dbname = "poputka_kz";

// Создаем подключение
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
?>
