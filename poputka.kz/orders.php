<?php
include('db_connection.php');
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id === null) {
    header("Location: login.php");
    exit();
}

// Фильтрация
$location_filter = $_GET['location'] ?? '';
$role_filter = $_GET['role'] ?? '';

$sql = "SELECT orders.*, users.username, users.phone FROM orders 
        LEFT JOIN users ON orders.user_id = users.id 
        WHERE 1=1";

if ($location_filter) {
    $escaped_location = $conn->real_escape_string($location_filter);
    $sql .= " AND orders.from_location LIKE '%" . $escaped_location . "%' ";
}

if ($role_filter) {
    $sql .= " AND orders.role = '" . $conn->real_escape_string($role_filter) . "'";
}

$sql .= " ORDER BY orders.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Попутка 24 - Список заказов</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
        }
        
        .top-nav {
            background: #34495e;
            padding: 15px 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }
        
        .nav-button {
            padding: 10px 20px;
            background-color: #425b74;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
        }
        
        .nav-button:hover {
            background-color: #2f435a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            color: white;
            text-align: center;
            margin: 30px 0;
            font-size: 2em;
        }
        
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .filter-card h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.3em;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
            font-size: 14px;
        }
        
        .autocomplete-container {
            position: relative;
            width: 100%;
        }
        
        input[type="text"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Montserrat', sans-serif;
            transition: border-color 0.3s;
        }
        
        input:focus,
        select:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .autocomplete-items {
            position: absolute;
            border: 1px solid #e0e0e0;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 200px;
            overflow-y: auto;
            background-color: white;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .autocomplete-items div {
            padding: 12px 15px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #f0f0f0;
            color: #2c3e50;
            transition: background-color 0.2s;
        }
        
        .autocomplete-items div:hover {
            background-color: #f8f9fa;
        }
        
        .autocomplete-active {
            background-color: #3498db !important;
            color: white !important;
        }
        
        .city-name {
            font-weight: 600;
            font-size: 15px;
        }
        
        .city-region {
            font-size: 13px;
            color: #7f8c8d;
            margin-top: 3px;
        }
        
        .autocomplete-active .city-region {
            color: #ecf0f1;
        }
        
        .filter-buttons {
            grid-column: 1 / -1;
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-primary {
            flex: 1;
            padding: 12px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #229954;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            flex: 1;
            padding: 12px;
            background-color: #95a5a6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        .order-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            margin-left: 10px;
        }
        
        .badge-driver-car {
            background-color: #3498db;
            color: white;
        }
        
        .badge-driver-truck {
            background-color: #e67e22;
            color: white;
        }
        
        .badge-passenger {
            background-color: #9b59b6;
            color: white;
        }
        
        .badge-cargo {
            background-color: #16a085;
            color: white;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: baseline;
            gap: 8px;
        }
        
        .info-label {
            font-weight: 600;
            color: #34495e;
            font-size: 14px;
        }
        
        .info-value {
            color: #2c3e50;
            font-size: 14px;
        }
        
        .order-description {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        
        .order-description .info-label {
            margin-bottom: 8px;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .order-actions button,
        .order-actions a {
            flex: 1;
            min-width: 140px;
            padding: 12px 20px;
            font-size: 14px;
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
        
        .no-results {
            text-align: center;
            color: white;
            font-size: 18px;
            margin: 60px 0;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
        }
        
        @media (max-width: 768px) {
            .nav-content {
                flex-direction: column;
            }
            
            .nav-button {
                width: 100%;
                text-align: center;
            }
            
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .order-info {
                grid-template-columns: 1fr;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .order-actions {
                flex-direction: column;
            }
            
            .order-actions button,
            .order-actions a {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="top-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-button">🏠 Главная</a>
            <a href="createOrder.php" class="nav-button">➕ Создать заказ</a>
            <a href="profile.php" class="nav-button">👤 Профиль</a>
        </div>
    </div>

    <div class="container">
        <h1>Список заказов</h1>

        <div class="filter-card">
            <h3>🔍 Поиск заказов</h3>
            <form method="GET" action="">
                <div class="filter-form">
                    <div class="form-group">
                        <label for="location">Населённый пункт:</label>
                        <div class="autocomplete-container">
                            <input type="text" name="location" id="location" value="<?= htmlspecialchars($location_filter) ?>" autocomplete="off" placeholder="Начните вводить название...">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="role">Роль:</label>
                        <select name="role" id="role">
                            <option value="">Все</option>
                            <option value="Водитель легкового" <?= $role_filter === 'Водитель легкового' ? 'selected' : '' ?>>Водитель легкового</option>
                            <option value="Водитель грузового" <?= $role_filter === 'Водитель грузового' ? 'selected' : '' ?>>Водитель грузового</option>
                            <option value="Попутчик" <?= $role_filter === 'Попутчик' ? 'selected' : '' ?>>Попутчик</option>
                            <option value="Попутный груз" <?= $role_filter === 'Попутный груз' ? 'selected' : '' ?>>Попутный груз</option>
                        </select>
                    </div>

                    <div class="filter-buttons">
                        <button type="submit" class="btn-primary">🔍 Найти</button>
                        <a href="orders.php" class="btn-secondary">🔄 Сбросить</a>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($order = $result->fetch_assoc()): 
                $badgeClass = '';
                switch($order['role']) {
                    case 'Водитель легкового':
                        $badgeClass = 'badge-driver-car';
                        break;
                    case 'Водитель грузового':
                        $badgeClass = 'badge-driver-truck';
                        break;
                    case 'Попутчик':
                        $badgeClass = 'badge-passenger';
                        break;
                    case 'Попутный груз':
                        $badgeClass = 'badge-cargo';
                        break;
                }
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <span class="badge <?= $badgeClass ?>">
                                <?= htmlspecialchars($order['role']) ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Создатель:</span>
                            <span class="info-value"><?= htmlspecialchars($order['username']) ?></span>
                        </div>
                    </div>
                    
                    <div class="order-info">
                        <div class="info-item">
                            <span class="info-label">📍 Откуда:</span>
                            <span class="info-value"><?= htmlspecialchars($order['from_location']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">📍 Куда:</span>
                            <span class="info-value"><?= htmlspecialchars($order['to_location']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">📅 Дата:</span>
                            <span class="info-value"><?= date('d.m.Y', strtotime($order['date'])) ?></span>
                        </div>
                        
                        <?php if ($order['passengers']): ?>
                            <div class="info-item">
                                <span class="info-label">👥 Мест:</span>
                                <span class="info-value"><?= $order['passengers'] ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($order['tonnage']): ?>
                            <div class="info-item">
                                <span class="info-label">⚖️ Тоннаж:</span>
                                <span class="info-value"><?= $order['tonnage'] ?> т</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($order['volume']): ?>
                            <div class="info-item">
                                <span class="info-label">📦 Объём:</span>
                                <span class="info-value"><?= $order['volume'] ?> м³</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-description">
                        <div class="info-label">📝 Описание:</div>
                        <div class="info-value"><?= htmlspecialchars($order['description']) ?></div>
                    </div>
                    
                    <div class="order-actions">
                        <a href="orderDetails.php?id=<?= $order['id'] ?>" class="btn-view">
                            👁️ Посмотреть
                        </a>
                        
                        <?php if ($order['user_id'] == $user_id): ?>
                            <a href="editOrder.php?id=<?= $order['id'] ?>" class="btn-edit">
                                ✏️ Редактировать
                            </a>
                            <form method="POST" action="deleteOrder.php" style="flex: 1; min-width: 140px;" onsubmit="return confirm('Вы уверены, что хотите удалить этот заказ?');">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <button type="submit" class="btn-delete" style="width: 100%;">
                                    🗑️ Удалить
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-results">
                <p> Заказов не найдено</p>
                <p style="font-size: 14px; margin-top: 10px; opacity: 0.8;">Попробуйте изменить параметры поиска</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        let cities = [];

        fetch('/cities.json')
            .then(response => response.json())
            .then(data => {
                cities = data.map(city => ({
                    name: city.name,
                    region: city.region,
                    fullName: `${city.name}, ${city.region}`
                }));
            })
            .catch(error => console.error('Ошибка загрузки городов:', error));

        function initAutocomplete() {
            const input = document.getElementById('location');
            let currentFocus = -1;
            
            input.addEventListener('input', function() {
                const value = this.value;
                closeAllLists();
                
                if (!value) return false;
                
                currentFocus = -1;
                
                const container = this.parentNode;
                const listDiv = document.createElement('div');
                listDiv.setAttribute('id', 'location-autocomplete-list');
                listDiv.setAttribute('class', 'autocomplete-items');
                container.appendChild(listDiv);
                
                const filtered = cities.filter(city => 
                    city.name.toLowerCase().includes(value.toLowerCase()) ||
                    city.region.toLowerCase().includes(value.toLowerCase())
                ).slice(0, 10);
                
                filtered.forEach(city => {
                    const itemDiv = document.createElement('div');
                    itemDiv.innerHTML = `
                        <div class="city-name">${city.name}</div>
                        <div class="city-region">${city.region}, Казахстан</div>
                    `;
                    
                    itemDiv.addEventListener('click', function() {
                        input.value = city.name;
                        closeAllLists();
                    });
                    
                    listDiv.appendChild(itemDiv);
                });
            });
            
            input.addEventListener('keydown', function(e) {
                let list = document.getElementById('location-autocomplete-list');
                if (list) list = list.getElementsByTagName('div');
                
                if (e.keyCode === 40) {
                    currentFocus++;
                    addActive(list);
                    e.preventDefault();
                } else if (e.keyCode === 38) {
                    currentFocus--;
                    addActive(list);
                    e.preventDefault();
                } else if (e.keyCode === 13) {
                    e.preventDefault();
                    if (currentFocus > -1 && list) {
                        list[currentFocus].click();
                    }
                }
            });
            
            function addActive(list) {
                if (!list) return false;
                removeActive(list);
                if (currentFocus >= list.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = list.length - 1;
                list[currentFocus].classList.add('autocomplete-active');
            }
            
            function removeActive(list) {
                for (let i = 0; i < list.length; i++) {
                    list[i].classList.remove('autocomplete-active');
                }
            }
            
            function closeAllLists(el) {
                const items = document.getElementsByClassName('autocomplete-items');
                for (let i = 0; i < items.length; i++) {
                    if (el !== items[i] && el !== input) {
                        items[i].parentNode.removeChild(items[i]);
                    }
                }
            }
            
            document.addEventListener('click', function(e) {
                closeAllLists(e.target);
            });
        }

        initAutocomplete();
    </script>
</body>
</html>

<?php
$conn->close();
?>