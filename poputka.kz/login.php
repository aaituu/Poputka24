<?php
session_start();

// Обработка формы авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Подключаемся к базе данных
    $conn = new mysqli('localhost', 'poputka_kz', 'plAEQeJRt77b2Da1', 'poputka_kz');
    if ($conn->connect_error) {
        die('Ошибка подключения: ' . $conn->connect_error);
    }

    // Проверяем, существует ли пользователь с таким email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Авторизация успешна, создаем сессию
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username']; // Добавляем имя пользователя в сессию
        header('Location: /index.php');
        exit();
    } else {
        echo 'Неверный email или пароль';
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <h1>Авторизация</h1>
    <form action="login.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Войти</button>
    </form>
    <p>Еще не зарегистрированы? <a href="register.php">Зарегистрироваться</a></p>
</body>
</html>
