<?php
// Параметры подключения к базе данных InfinityFree
$servername = "sql303.infinityfree.com";
$username = "if0_40740361";
$password = "9r6mEbm5yS";
$dbname = "if0_40740361_poputka24"; // Замените XXX на реальное имя вашей БД

// Создаем подключение
$conn = new mysqli($servername, $username, $password, $dbname);

// Устанавливаем кодировку UTF-8
$conn->set_charset("utf8mb4");

// Проверка подключения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
?>