<?php
// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];

    // Подключаемся к базе данных InfinityFree
    $conn = new mysqli('sql303.infinityfree.com', 'if0_40740361', '9r6mEbm5yS', 'if0_40740361_poputka24');
    
    // Устанавливаем кодировку
    $conn->set_charset("utf8mb4");
    
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
        if ($stmt->execute()) {
            echo 'Регистрация прошла успешно! <a href="login.php">Войти</a>';
        } else {
            echo 'Ошибка регистрации: ' . $conn->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Попутка 24 - Регистрация</title>
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
        <input type="text" name="phone" required>

        <label>
            <input type="checkbox" required>
            Я прочитал(а) и согласен(на) с <a href="/terms.html" target="_blank">Условиями оферты</a> и <a href="/privacy.html" target="_blank">Политикой конфиденциальности</a>.
        </label><br><br>

        <button type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже зарегистрированы? <a href="login.php">Войти</a></p>
</body>
</html>