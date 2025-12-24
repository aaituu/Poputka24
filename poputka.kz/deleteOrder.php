<?php
include('db_connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    
    // Проверяем, что заказ принадлежит текущему пользователю
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['message'] = "Заказ успешно удален.";
    } else {
        $_SESSION['error'] = "Ошибка при удалении заказа или у вас нет прав.";
    }
    
    $stmt->close();
}

$conn->close();

// Определяем, откуда пришел запрос
$referer = $_SERVER['HTTP_REFERER'] ?? 'profile.php';
if (strpos($referer, 'orders.php') !== false) {
    header("Location: orders.php");
} else {
    header("Location: profile.php");
}
exit();
?>