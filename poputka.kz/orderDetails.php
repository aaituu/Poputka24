<?php
// Подключаемся к базе данных
$conn = new mysqli('localhost', 'poputka_kz', 'plAEQeJRt77b2Da1', 'poputka_kz');
if ($conn->connect_error) {
    die('Ошибка подключения: ' . $conn->connect_error);
}

// Инициализируем сессию
session_start();
if (!isset($_SESSION['user_id'])) {
    die('Вы должны войти в систему, чтобы принять заказ.');
}

$user_id = $_SESSION['user_id']; // ID текущего пользователя
$order_id = $_GET['id'] ?? null;

// Проверяем, был ли отправлен запрос на принятие заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_order'])) {
    // Проверяем, что заказ существует и не принят
    $check_sql = "SELECT * FROM orders WHERE id = ? AND accepted_by IS NULL";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if ($order) {
        // Обновляем заказ, отмечаем его как принятый текущим пользователем
        $update_sql = "UPDATE orders SET accepted_by = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ii', $user_id, $order_id);
        if ($stmt->execute()) {
            echo "Заказ успешно принят!";
        } else {
            echo "Ошибка при принятии заказа: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Этот заказ уже принят или не существует.";
    }
}

// Получаем данные о заказе и информацию о создателе
$sql = "
    SELECT 
        orders.*, 
        users.phone AS creator_phone 
    FROM 
        orders 
    LEFT JOIN 
        users 
    ON 
        orders.user_id = users.id 
    WHERE 
        orders.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Детали заказа</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/css/orderDetail.css">
</head>
<body>
    <?php if ($order): ?>
        <h1>Детали заказа</h1>
        <p><strong>Тип:</strong> <?= htmlspecialchars($order['type']); ?></p>
        <p><strong>Область:</strong> <?= htmlspecialchars($order['region']); ?></p>
        <p><strong>Откуда:</strong> <?= htmlspecialchars($order['from_location']); ?></p>
        <p><strong>Куда:</strong> <?= htmlspecialchars($order['to_location']); ?></p>
        <p><strong>Описание:</strong> <?= htmlspecialchars($order['description']); ?></p>
        <p><strong>Принято:</strong> <?= $order['accepted_by'] ? 'Да' : 'Нет'; ?></p>

        <?php if (!$order['accepted_by']): ?>
            <form method="POST">
                <button type="submit" name="accept_order">Принять заказ</button>
            </form>
        <?php else: ?>
            <p>Заказ принят!</p>
            <p><strong>Номер телефона создателя заказа:</strong> <?= htmlspecialchars($order['creator_phone'] ?? 'Нет данных'); ?></p>
        <?php endif; ?>

        <a href="/orders.php">Вернуться к списку заказов</a>
    <?php else: ?>
        <p>Заказ не найден.</p>
    <?php endif; ?>
</body>
</html>
