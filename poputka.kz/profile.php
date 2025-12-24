<?php
include('db_connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Вы должны войти в систему для доступа к профилю.');
}

$user_id = $_SESSION['user_id'];

// Получаем все заказы пользователя
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Попутка 24 - Мой профиль</title>
    <link rel="stylesheet" href="/css/profile.css">
    <style>
        .order-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .order-info {
            margin-bottom: 15px;
        }
        
        .order-info p {
            margin: 8px 0;
            font-size: 16px;
            text-align: left;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }
        
        .order-actions button, .order-actions a {
            flex: 1;
            max-width: 200px;
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background-color: #3498db;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #2980b9;
            transform: scale(1.05);
        }
        
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #c0392b;
            transform: scale(1.05);
        }
        
        .no-orders {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-size: 18px;
        }
        
        .back-link {
            display: inline-block;
            margin: 20px 0;
            padding: 12px 24px;
            background-color: #34495e;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .back-link:hover {
            background-color: #2c3e50;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .badge-truck {
            background-color: #f39c12;
            color: white;
        }
        
        .badge-car {
            background-color: #3498db;
            color: white;
        }
        
        .badge-status {
            background-color: #27ae60;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Мой профиль</h1>
    <p><strong>Имя пользователя:</strong> <?= htmlspecialchars($_SESSION['username']); ?></p>
    
    <a href="index.php" class="back-link">← Вернуться на главную</a>

    <h2>Мои заказы</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <?php while ($order = $result->fetch_assoc()): ?>
            <div class="order-card">
                <div class="order-info">
                    <p>
                        <strong>Тип:</strong> <?= htmlspecialchars($order['type']); ?>
                        <span class="badge <?= $order['type'] === 'Грузовой' ? 'badge-truck' : 'badge-car' ?>">
                            <?= $order['type'] ?>
                        </span>
                        <?php if ($order['accepted_by']): ?>
                            <span class="badge badge-status">Принят</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Область:</strong> <?= htmlspecialchars($order['region']); ?></p>
                    <p><strong>Откуда:</strong> <?= htmlspecialchars($order['from_location']); ?></p>
                    <p><strong>Куда:</strong> <?= htmlspecialchars($order['to_location']); ?></p>
                    <p><strong>Дата:</strong> <?= date('d.m.Y', strtotime($order['date'])); ?></p>
                    <p><strong>Роль:</strong> <?= htmlspecialchars($order['role']); ?></p>
                    
                    <?php if ($order['type'] === 'Легковой' && $order['passengers']): ?>
                        <p><strong>Пассажиров:</strong> <?= $order['passengers']; ?></p>
                    <?php endif; ?>
                    
                    <?php if ($order['type'] === 'Грузовой'): ?>
                        <?php if ($order['tonnage']): ?>
                            <p><strong>Тоннаж:</strong> <?= $order['tonnage']; ?> тонн</p>
                        <?php endif; ?>
                        <?php if ($order['volume']): ?>
                            <p><strong>Объём:</strong> <?= $order['volume']; ?> м³</p>
                        <?php endif; ?>
                        <?php if ($order['cargo_type']): ?>
                            <p><strong>Тип груза:</strong> <?= htmlspecialchars($order['cargo_type']); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <p><strong>Описание:</strong> <?= htmlspecialchars($order['description']); ?></p>
                </div>
                
                <div class="order-actions">
                    <a href="editOrder.php?id=<?= $order['id']; ?>" class="btn-edit">
                        ✏️ Редактировать
                    </a>
                    <form method="POST" action="deleteOrder.php" style="flex: 1; max-width: 200px;" onsubmit="return confirm('Вы уверены, что хотите удалить этот заказ?');">
                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                        <button type="submit" class="btn-delete">
                            🗑️ Удалить
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-orders">
            <p>У вас пока нет созданных заказов.</p>
            <a href="createOrder.php" class="back-link">Создать первый заказ</a>
        </div>
    <?php endif; ?>
</body>
</html>