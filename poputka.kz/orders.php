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
        body {
            background-color: #2C3E50;
            padding: 0;
            margin: 0;
        }
        
        .top-nav {
            background: #34495e;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-left {
            display: flex;
            gap: 15px;
        }
        
        .nav-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #425b74;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 15px;
        }
        
        .nav-button:hover {
            background-color: #2f435aff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .nav-button.secondary {
            background-color: #425b74;
        }
        
        .nav-button.secondary:hover {
            background-color: #2f435aff;
        }
        
        h1 {
            color: white;
            text-align: center;
            margin: 30px 0;
            font-size: 2.2em;
        }
        
        .order-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin: 15px auto;
            max-width: 900px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .order-info p {
            margin: 8px 0;
            font-size: 16px;
            color: #2c3e50;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .badge-truck {
            background-color: #ac8341ff;
            color: white;
        }
        
        .badge-car {
            background-color: #34495e;
            color: white;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .order-actions button, .order-actions a {
            flex: 1;
            min-width: 140px;
            max-width: 180px;
            padding: 10px 18px;
            font-size: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            color: white;
            font-weight: 600;
        }
        
        .btn-view {
            background-color: #2c7149ff;
        }
        
        .btn-view:hover {
            background-color: rgba(20, 77, 44, 1)ff;
            transform: scale(1.05);
        }
        
        .btn-edit {
            background-color: #34495e;
        }
        
        .btn-edit:hover {
            background-color: #2a3a4aff;
            transform: scale(1.05);
        }
        
        .btn-delete {
            background-color: #853f38ff;
        }
        
        .btn-delete:hover {
            background-color: #6a0e04ff;
            transform: scale(1.05);
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin: 20px auto;
            max-width: 900px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filter-section h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.3em;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .filter-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #34495e;
        }
        
        .filter-form input, .filter-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 15px;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .no-results {
            text-align: center;
            color: white;
            font-size: 20px;
            margin: 60px 0;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            max-width: 600px;
            margin: 60px auto;
        }
        
        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                flex-direction: column;
            }
            
            .order-actions button, .order-actions a {
                max-width: 100%;
            }
            
            .nav-left {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            .nav-button {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="top-nav">
        <div class="nav-left">
            <a href="index.php" class="nav-button"> Главная</a>
            <a href="createOrder.php" class="nav-button secondary"> Создать заказ</a>
        </div>
    </div>

    <h1>Список заказов</h1>

    <div class="filter-section">
        <h3>🔍 Фильтры поиска</h3>
        <form method="GET" action="">
            <div class="filter-form">
                <div>
                    <label for="type">Тип:</label>
                    <select name="type" id="type">
                        <option value="">Все</option>
                        <option value="Грузовой" <?= $type_filter === 'Грузовой' ? 'selected' : '' ?>>Грузовой</option>
                        <option value="Легковой" <?= $type_filter === 'Легковой' ? 'selected' : '' ?>>Легковой</option>
                    </select>
                </div>

                <div>
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
                </div>

                <div>
                    <label for="role">Кого ищете?:</label>
                    <select name="role" id="role">
                        <option value="">Все</option>
                        <option value="Попутчик" <?= $role_filter === 'Попутчик' ? 'selected' : '' ?>>Попутчик</option>
                        <option value="Водитель" <?= $role_filter === 'Водитель' ? 'selected' : '' ?>>Водитель</option>
                    </select>
                </div>
                
                <div>
                    <label for="from_filter">Откуда:</label>
                    <input type="text" name="from_filter" id="from_filter" value="<?= htmlspecialchars($from_filter) ?>" placeholder="Город отправления">
                </div>
                
                <div>
                    <label for="to_filter">Куда:</label>
                    <input type="text" name="to_filter" id="to_filter" value="<?= htmlspecialchars($to_filter) ?>" placeholder="Город назначения">
                </div>
            </div>

            <div class="filter-buttons">
                <button type="submit" class="nav-button secondary" style="flex: 1;">🔍 Применить фильтр</button>
                <a href="orders.php" class="nav-button" style="flex: 1; display: inline-block;">🔄 Сбросить</a>
            </div>
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
                    <p><strong>🏛️ Область:</strong> <?= htmlspecialchars($order['region']); ?></p>
                    <p><strong>📍 Откуда:</strong> <?= htmlspecialchars($order['from_location']); ?></p>
                    <p><strong>📍 Куда:</strong> <?= htmlspecialchars($order['to_location']); ?></p>
                    <p><strong>📅 Дата:</strong> <?= date('d.m.Y', strtotime($order['date'])); ?></p>
                    <p><strong>👤 Роль:</strong> <?= htmlspecialchars($order['role']); ?></p>
                    
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
                    
                    <p><strong> Описание:</strong> <?= htmlspecialchars($order['description']); ?></p>
                </div>
                
                <div class="order-actions">
                    <a href="orderDetails.php?id=<?= $order['id']; ?>" class="btn-view">
                        Посмотреть
                    </a>
                    
                    <?php if ($order['user_id'] == $user_id): ?>
                        <a href="editOrder.php?id=<?= $order['id']; ?>" class="btn-edit">
                            Редактировать
                        </a>
                        <form method="POST" action="deleteOrder.php" style="flex: 1; max-width: 180px; min-width: 140px;" onsubmit="return confirm('Вы уверены, что хотите удалить этот заказ?');">
                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                            <button type="submit" class="btn-delete">
                                Удалить
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-results">
            <p>Нет заказов, соответствующих фильтрам.</p>
            <p style="font-size: 16px; margin-top: 10px;">Попробуйте изменить параметры поиска</p>
        </div>
    <?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>