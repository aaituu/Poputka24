<?php
include('db_connection.php');
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id === null) {
    header("Location: login.php");
    exit();
}

// Фильтрация
$type_filter = $_GET['type'] ?? '';
$region_filter = $_GET['region'] ?? '';
$role_filter = $_GET['role'] ?? '';
$from_filter = $_GET['from_filter'] ?? '';
$to_filter = $_GET['to_filter'] ?? '';

$sql = "SELECT orders.*, users.username, users.phone FROM orders 
        LEFT JOIN users ON orders.user_id = users.id 
        WHERE 1=1";

if ($type_filter) {
    $sql .= " AND orders.type = '" . $conn->real_escape_string($type_filter) . "'";
}

if ($region_filter) {
    $sql .= " AND orders.region = '" . $conn->real_escape_string($region_filter) . "'";
}

if ($role_filter) {
    $sql .= " AND orders.role = '" . $conn->real_escape_string($role_filter) . "'";
}

if ($from_filter) {
    $sql .= " AND orders.from_location LIKE '%" . $conn->real_escape_string($from_filter) . "%'";
}

if ($to_filter) {
    $sql .= " AND orders.to_location LIKE '%" . $conn->real_escape_string($to_filter) . "%'";
}

$sql .= " ORDER BY orders.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Попутка 24 - Список заказов</title>
    <link rel="stylesheet" href="/css/orders.css">
    <style>
        .order-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }
        
        .order-info p {
            margin: 8px 0;
            font-size: 16px;
            color: #2c3e50;
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
        
        .order-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }
        
        .order-actions button, .order-actions a {
            flex: 1;
            max-width: 180px;
            padding: 10px 18px;
            font-size: 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-view {
            background-color: #27ae60;
        }
        
        .btn-view:hover {
            background-color: #229954;
            transform: scale(1.05);
        }
        
        .btn-edit {
            background-color: #3498db;
        }
        
        .btn-edit:hover {
            background-color: #2980b9;
            transform: scale(1.05);
        }
        
        .btn-delete {
            background-color: #e74c3c;
        }
        
        .btn-delete:hover {
            background-color: #c0392b;
            transform: scale(1.05);
        }
        
        .filter-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filter-form input, .filter-form select {
            margin: 5px;
            padding: 8px;
        }
    </style>
</head>
<body>
    <h1>Список заказов</h1>

    <div class="filter-form">
        <form method="GET" action="">
            <label for="type">Тип:</label>
            <select name="type" id="type">
                <option value="">Все</option>
                <option value="Грузовой" <?= $type_filter === 'Грузовой' ? 'selected' : '' ?>>Грузовой</option>
                <option value="Легковой" <?= $type_filter === 'Легковой' ? 'selected' : '' ?>>Легковой</option>
            </select>

            <label for="region">Область:</label>
            <select name="region" id="region">
                <option value="">Все</option>
                <?php
                $regions = [
                    "Акмолинская область", "Улытауская область", "Абайская область", "Жетысуйская область",
                    "Актюбинская область", "Алматинская область", "Атырауская область", 
                    "Восточно-Казахстанская область", "Жамбылская область", "Западно-Казахстанская область",
                    "Карагандинская область", "Костанайская область", "Кызылординская область",
                    "Мангистауская область", "Павлодарская область", "Северо-Казахстанская область",
                    "Туркестанская область"
                ];
                foreach ($regions as $region) {
                    $selected = ($region_filter === $region) ? 'selected' : '';
                    echo "<option value='$region' $selected>$region</option>";
                }
                ?>
            </select>

            <label for="role">Кого ищете?:</label>
            <select name="role" id="role">
                <option value="">Все</option>
                <option value="Попутчик" <?= $role_filter === 'Попутчик' ? 'selected' : '' ?>>Попутчик</option>
                <option value="Водитель" <?= $role_filter === 'Водитель' ? 'selected' : '' ?>>Водитель</option>
            </select>
            
            <label for="from_filter">Откуда:</label>
            <input type="text" name="from_filter" id="from_filter" value="<?= htmlspecialchars($from_filter) ?>" placeholder="Город отправления">
            
            <label for="to_filter">Куда:</label>
            <input type="text" name="to_filter" id="to_filter" value="<?= htmlspecialchars($to_filter) ?>" placeholder="Город назначения">

            <button type="submit">🔍 Применить фильтр</button>
            <a href="orders.php"><button type="button">🔄 Сбросить</button></a>
        </form>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($order = $result->fetch_assoc()): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <strong><?= htmlspecialchars($order['type']); ?></strong>
                        <span class="badge <?= $order['type'] === 'Грузовой' ? 'badge-truck' : 'badge-car' ?>">
                            <?= $order['type'] ?>
                        </span>
                    </div>
                    <div>
                        <strong>Создатель:</strong> <?= htmlspecialchars($order['username']); ?>
                    </div>
                </div>
                
                <div class="order-info">
                    <p><strong>Область:</strong> <?= htmlspecialchars($order['region']); ?></p>
                    <p><strong>📍 Откуда:</strong> <?= htmlspecialchars($order['from_location']); ?></p>
                    <p><strong>📍 Куда:</strong> <?= htmlspecialchars($order['to_location']); ?></p>
                    <p><strong>📅 Дата:</strong> <?= date('d.m.Y', strtotime($order['date'])); ?></p>
                    <p><strong>Роль:</strong> <?= htmlspecialchars($order['role']); ?></p>
                    
                    <?php if ($order['type'] === 'Легковой' && $order['passengers']): ?>
                        <p><strong>👥 Пассажиров:</strong> <?= $order['passengers']; ?></p>
                    <?php endif; ?>
                    
                    <?php if ($order['type'] === 'Грузовой'): ?>
                        <?php if ($order['tonnage']): ?>
                            <p><strong>⚖️ Тоннаж:</strong> <?= $order['tonnage']; ?> тонн</p>
                        <?php endif; ?>
                        <?php if ($order['volume']): ?>
                            <p><strong>📦 Объём:</strong> <?= $order['volume']; ?> м³</p>
                        <?php endif; ?>
                        <?php if ($order['cargo_type']): ?>
                            <p><strong>📋 Тип груза:</strong> <?= htmlspecialchars($order['cargo_type']); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <p><strong>Описание:</strong> <?= htmlspecialchars($order['description']); ?></p>
                </div>
                
                <div class="order-actions">
                    <a href="orderDetails.php?id=<?= $order['id']; ?>" class="btn-view">
                        👁️ Посмотреть
                    </a>
                    
                    <?php if ($order['user_id'] == $user_id): ?>
                        <a href="editOrder.php?id=<?= $order['id']; ?>" class="btn-edit">
                            ✏️ Редактировать
                        </a>
                        <form method="POST" action="deleteOrder.php" style="flex: 1; max-width: 180px;" onsubmit="return confirm('Вы уверены, что хотите удалить этот заказ?');">
                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                            <button type="submit" class="btn-delete">
                                🗑️ Удалить
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align: center; color: white; font-size: 18px; margin: 40px 0;">Нет заказов, соответствующих фильтрам.</p>
    <?php endif; ?>

    <div class="glav">
        <a href="index.php">На Главную</a>
    </div>

</body>
</html>

<?php
$conn->close();
?>