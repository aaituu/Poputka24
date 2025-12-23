<?php
session_start();

// Обработка формы авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Подключаемся к базе данных InfinityFree
    $conn = new mysqli('sql303.infinityfree.com', 'if0_40740361', '9r6mEbm5yS', 'if0_40740361_poputka24');
    
    // Устанавливаем кодировку
    $conn->set_charset("utf8mb4");
    
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
        $_SESSION['username'] = $user['username'];
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
    <title>Попутка 24 - Авторизация</title>
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