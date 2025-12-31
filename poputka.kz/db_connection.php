<?php
// Параметры подключения к базе данных
$servername = "localhost";
$username = "poputka1_user";
$password = "a16e*6bG8!Yu"; // Замените на ваш реальный пароль
$dbname = "poputka1_db";
$port = 3306;

// Создаем подключение
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Устанавливаем кодировку UTF-8
$conn->set_charset("utf8mb4");

// Проверка подключения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
?>