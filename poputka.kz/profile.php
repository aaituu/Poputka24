<?php
// Подключаемся к базе данных
$conn = new mysqli('localhost', 'poputka_kz', 'plAEQeJRt77b2Da1', 'poputka_kz');
if ($conn->connect_error) {
    die('Ошибка подключения: ' . $conn->connect_error);
}

// Инициализируем сессию
session_start();
if (!isset($_SESSION['user_id'])) {
    die('Вы должны войти в систему для доступа к профилю.');
}

$user_id = $_SESSION['user_id']; // ID текущего пользователя

$sqlAccepted = "
    SELECT 
        orders.*, 
        users.phone AS creator_phone, 
        users.username AS creator_name
    FROM 
        orders
    JOIN 
        users ON orders.user_id = users.id
    WHERE 
        orders.accepted_by = ?";
$stmtAccepted = $conn->prepare($sqlAccepted);
$stmtAccepted->bind_param('i', $user_id);
$stmtAccepted->execute();
$resultAccepted = $stmtAccepted->get_result();
$stmtAccepted->close();


// Получаем заказы, которые создал пользователь, но их принял другой
$sqlCreated = "SELECT * FROM orders WHERE user_id = ? AND accepted_by IS NOT NULL";
$stmtCreated = $conn->prepare($sqlCreated);
$stmtCreated->bind_param('i', $user_id);
$stmtCreated->execute();
$resultCreated = $stmtCreated->get_result();
$stmtCreated->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мой профиль</title>
    <link rel="stylesheet" href="/css/profile.css">
</head>
<body>
    <h1>Мой профиль</h1>
    <p><strong>Имя пользователя:</strong> <?= htmlspecialchars($_SESSION['username']); ?></p>

    <h2>Принятые заказы</h2>
    <ul>
        <?php if ($resultAccepted->num_rows > 0): ?>
            <?php while ($order = $resultAccepted->fetch_assoc()): ?>
                <li>
                    <strong>Тип:</strong> <?= htmlspecialchars($order['type']); ?> |
                    <strong>Область:</strong> <?= htmlspecialchars($order['region']); ?> |
                    <strong>Откуда:</strong> <?= htmlspecialchars($order['from_location']); ?> |
                    <strong>Куда:</strong> <?= htmlspecialchars($order['to_location']); ?> |
                    <strong>Описание:</strong> <?= htmlspecialchars($order['description']); ?>
                    <br>
                    <strong>Создатель заказа:</strong> <?= htmlspecialchars($order['creator_name']); ?>
                    <br>
                    <strong>Номер телефона:</strong> 
                    <a href="tel:<?= htmlspecialchars($order['creator_phone']); ?>">
                        <?= htmlspecialchars($order['creator_phone']); ?>
                    </a>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li>Вы ещё не приняли ни одного заказа.</li>
        <?php endif; ?>
    </ul>


    <h2>Созданные заказы, принятые другими</h2>
    <ul>
        <?php if ($resultCreated->num_rows > 0): ?>
            <?php while ($order = $resultCreated->fetch_assoc()): ?>
                <li>
                    <strong>Тип:</strong> <?= htmlspecialchars($order['type']); ?> |
                    <strong>Область:</strong> <?= htmlspecialchars($order['region']); ?> |
                    <strong>Откуда:</strong> <?= htmlspecialchars($order['from_location']); ?> |
                    <strong>Куда:</strong> <?= htmlspecialchars($order['to_location']); ?> |
                    <strong>Описание:</strong> <?= htmlspecialchars($order['description']); ?>
                    <br>
                    <strong>Принял пользователь ID:</strong> <?= htmlspecialchars($order['accepted_by']); ?>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li>Нет созданных вами заказов, которые приняты другими пользователями.</li>
        <?php endif; ?>
    </ul>
</body>
</html>
