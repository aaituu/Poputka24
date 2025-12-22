<?php
// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Хэшируем пароль
    $phone = $_POST['phone'];

    // Подключаемся к базе данных
    $conn = new mysqli('localhost', 'poputka_kz', 'plAEQeJRt77b2Da1', 'poputka_kz');
    if ($conn->connect_error) {
        die('Ошибка подключения: ' . $conn->connect_error);
    }

    // Проверяем, существует ли уже пользователь с таким email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo 'Пользователь с таким email уже существует';
    } else {
        // Добавляем нового пользователя в базу данных
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $phone);
        $stmt->execute();
        echo 'Регистрация прошла успешно';
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/register.css">
</head>
<body>
    <h1>Регистрация</h1>
    <form action="register.php" method="POST">
        <label for="username">Имя пользователя:</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>

        <label for="phone">Телефон:</label>
        <input type="text" name="phone" required> <!-- Поле для телефона -->

        <label>
            <input type="checkbox" required>
            Я прочитал(а) и согласен(на) с <a href="/terms" target="_blank">Условиями оферты</a> и <a href="/privacy" target="_blank">Политикой конфиденциальности</a>.
        </label><br><br>

        <button type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже зарегистрированы? <a href="login.php">Войти</a></p>
</body>
</html>
