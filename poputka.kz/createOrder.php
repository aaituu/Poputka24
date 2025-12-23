<?php
// Включаем файл для подключения к базе данных
include('db_connection.php');

// Инициализируем сессию
session_start();

// Если не авторизован, перенаправляем на страницу логина
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Получаем ID пользователя из сессии
$user_id = $_SESSION['user_id'];

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы с проверкой наличия
    $type = $_POST['type'] ?? null;
    $region = $_POST['region'] ?? null;
    $from_location = $_POST['from'] ?? null;
    $to_location = $_POST['to'] ?? null;
    $description = $_POST['description'] ?? null;
    $role = $_POST['role'] ?? null;

    // Проверяем, заполнены ли все обязательные поля
    if (!$type || !$region || !$from_location || !$to_location || !$description || !$role) {
        echo "Ошибка: заполните все обязательные поля.";
        exit();
    }

    // Используем подготовленный запрос для безопасной вставки данных
    $stmt = $conn->prepare("INSERT INTO orders (user_id, type, region, from_location, to_location, description, role) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $type, $region, $from_location, $to_location, $description, $role);

    // Выполняем запрос и обрабатываем результат
    if ($stmt->execute()) {
        header("Location: orders.php");
        exit();
    } else {
        header("Location: error.php?message=" . urlencode($stmt->error));
        exit();
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Попутка 24 - Создание заказа</title>
    <link rel="stylesheet" href="/css/ordersCreate.css">
    <script>
        // Дополнительная проверка на стороне клиента
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                const from = document.querySelector('input[name="from"]').value;
                const to = document.querySelector('input[name="to"]').value;

                if (!from || !to) {
                    alert('Поля "Откуда" и "Куда" обязательны для заполнения!');
                    event.preventDefault();
                }
            });
        });
    </script>
</head>
<body>
    <main>
        <h1>Создание заказа</h1>

        <form action="createOrder.php" method="POST">
            <label for="type">Тип:</label>
            <select name="type" required>
                <option value="Грузовой">Грузовой</option>
                <option value="Легковой">Легковой</option>
            </select>
            <br>

            <label for="region">Область:</label>
            <select name="region" required>
                <option value="Акмолинская область">Акмолинская область</option>
                <option value="Улытауская область">Улытауская область</option>
                <option value="Абайская область">Абайская область</option>
                <option value="Жетысуйская область">Жетысуйская область</option>
                <option value="Актюбинская область">Актюбинская область</option>
                <option value="Алматинская область">Алматинская область</option>
                <option value="Атырауская область">Атырауская область</option>
                <option value="Восточно-Казахстанская область">Восточно-Казахстанская область</option>
                <option value="Жамбылская область">Жамбылская область</option>
                <option value="Западно-Казахстанская область">Западно-Казахстанская область</option>
                <option value="Карагандинская область">Карагандинская область</option>
                <option value="Костанайская область">Костанайская область</option>
                <option value="Кызылординская область">Кызылординская область</option>
                <option value="Мангистауская область">Мангистауская область</option>
                <option value="Павлодарская область">Павлодарская область</option>
                <option value="Северо-Казахстанская область">Северо-Казахстанская область</option>
                <option value="Туркестанская область">Туркестанская область</option>
            </select>
            <br>

            <label for="from">Откуда:</label>
            <input type="text" name="from" required>
            <br>

            <label for="to">Куда:</label>
            <input type="text" name="to" required>
            <br>

            <label for="description">Описание:</label>
            <textarea name="description" required></textarea>
            <br>

            <label for="role">Роль:</label>
            <select name="role" required>
                <option value="Попутчик">Попутчик</option>
                <option value="Водитель">Водитель</option>
            </select>
            <br>

            <button type="submit" class="CreateBtn">Создать заказ</button>
        </form>
    </main>
</body>
</html>