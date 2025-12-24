<?php
include('db_connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header("Location: orders.php");
    exit();
}

// Получаем заказ
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Заказ не найден или у вас нет прав на его редактирование.");
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_location = $_POST['from'] ?? null;
    $to_location = $_POST['to'] ?? null;
    $date = $_POST['date'] ?? null;
    $description = $_POST['description'] ?? null;
    $role = $_POST['role'] ?? null;
    
    $passengers = ($role === 'Водитель легкового') ? ($_POST['passengers'] ?? null) : null;
    $tonnage = ($role === 'Водитель грузового') ? ($_POST['tonnage'] ?? null) : null;
    $volume = ($role === 'Водитель грузового') ? ($_POST['volume'] ?? null) : null;
    
    // Определяем тип
    if ($role === 'Водитель легкового' || $role === 'Попутчик') {
        $type = 'Легковой';
    } else {
        $type = 'Грузовой';
    }
    
    // Извлекаем область из населенного пункта
    $from_parts = explode(', ', $from_location);
    $region = isset($from_parts[1]) ? $from_parts[1] : '';

    if (!$from_location || !$to_location || !$date || !$role) {
        $error = "Ошибка: заполните все обязательные поля.";
    } else {
        $stmt = $conn->prepare("UPDATE orders SET type=?, region=?, from_location=?, to_location=?, date=?, description=?, role=?, passengers=?, tonnage=?, volume=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssssssiddii", $type, $region, $from_location, $to_location, $date, $description, $role, $passengers, $tonnage, $volume, $order_id, $user_id);

        if ($stmt->execute()) {
            header("Location: profile.php");
            exit();
        } else {
            $error = "Ошибка при обновлении заказа.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Попутка 24 - Редактирование заказа</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2em;
        }
        
        .form-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        
        .error-message {
            background-color: #e74c3c;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 15px;
        }
        
        input[type="text"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Montserrat', sans-serif;
            transition: border-color 0.3s;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .autocomplete-container {
            position: relative;
        }
        
        .autocomplete-items {
            position: absolute;
            border: 1px solid #e0e0e0;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 250px;
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
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        button {
            flex: 1;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }
        
        .btn-submit {
            background-color: #3498db;
            color: white;
        }
        
        .btn-submit:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        }
        
        .btn-cancel {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-cancel:hover {
            background-color: #7f8c8d;
        }
        
        .dynamic-fields {
            display: none;
            animation: fadeIn 0.3s;
        }
        
        .dynamic-fields.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .form-card {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.5em;
            }
            
            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Редактирование заказа</h1>
        
        <div class="form-card">
            <?php if (isset($error)): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>

            <form id="orderForm" action="editOrder.php?id=<?= $order_id ?>" method="POST">
                <div class="form-group">
                    <label for="role">Я:</label>
                    <select name="role" id="role" required onchange="updateForm()">
                        <option value="Водитель легкового" <?= $order['role'] === 'Водитель легкового' ? 'selected' : '' ?>>Водитель легкового</option>
                        <option value="Водитель грузового" <?= $order['role'] === 'Водитель грузового' ? 'selected' : '' ?>>Водитель грузового</option>
                        <option value="Попутчик" <?= $order['role'] === 'Попутчик' ? 'selected' : '' ?>>Попутчик</option>
                        <option value="Попутный груз" <?= $order['role'] === 'Попутный груз' ? 'selected' : '' ?>>Попутный груз</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="from">Откуда:</label>
                    <div class="autocomplete-container">
                        <input type="text" name="from" id="from" value="<?= htmlspecialchars($order['from_location']) ?>" required autocomplete="off" placeholder="Начните вводить название населённого пункта...">
                    </div>
                </div>

                <div class="form-group">
                    <label for="to">Куда:</label>
                    <div class="autocomplete-container">
                        <input type="text" name="to" id="to" value="<?= htmlspecialchars($order['to_location']) ?>" required autocomplete="off" placeholder="Начните вводить название населённого пункта...">
                    </div>
                </div>

                <div class="form-group">
                    <label for="date">Дата:</label>
                    <input type="date" name="date" id="date" value="<?= $order['date'] ?>" required>
                </div>

                <div id="carFields" class="dynamic-fields <?= ($order['role'] === 'Водитель легкового' || $order['role'] === 'Попутчик') ? 'active' : '' ?>">
                    <div class="form-group">
                        <label for="passengers">Количество мест:</label>
                        <input type="number" name="passengers" id="passengers" value="<?= $order['passengers'] ?>" min="1" max="20" placeholder="Например: 3">
                    </div>
                </div>

                <div id="truckFields" class="dynamic-fields <?= ($order['role'] === 'Водитель грузового' || $order['role'] === 'Попутный груз') ? 'active' : '' ?>">
                    <div class="form-group">
                        <label for="tonnage">Грузоподъёмность (тонн):</label>
                        <input type="number" name="tonnage" id="tonnage" value="<?= $order['tonnage'] ?>" step="0.1" min="0.1" placeholder="Например: 5">
                    </div>

                    <div class="form-group">
                        <label for="volume">Объём кузова (м³):</label>
                        <input type="number" name="volume" id="volume" value="<?= $order['volume'] ?>" step="0.1" min="0.1" placeholder="Например: 20">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Описание:</label>
                    <textarea name="description" id="description" required placeholder="Укажите детали поездки: время отправления, особые условия и т.д."><?= htmlspecialchars($order['description']) ?></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-submit">💾 Сохранить изменения</button>
                    <a href="profile.php" style="flex: 1; text-decoration: none;">
                        <button type="button" class="btn-cancel" style="width: 100%;">❌ Отмена</button>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let cities = [];

        fetch('/cities.json')
            .then(response => response.json())
            .then(data => {
                cities = data.map(city => ({
                    name: city.name,
                    region: city.region,
                    fullName: `${city.name}, ${city.region}, Казахстан`
                }));
            })
            .catch(error => console.error('Ошибка загрузки городов:', error));

        function updateForm() {
            const role = document.getElementById('role').value;
            const carFields = document.getElementById('carFields');
            const truckFields = document.getElementById('truckFields');
            const passengersInput = document.getElementById('passengers');
            const tonnageInput = document.getElementById('tonnage');
            const volumeInput = document.getElementById('volume');

            carFields.classList.remove('active');
            truckFields.classList.remove('active');

            if (role === 'Водитель легкового' || role === 'Попутчик') {
                carFields.classList.add('active');
                passengersInput.required = true;
                tonnageInput.required = false;
                volumeInput.required = false;
            } else if (role === 'Водитель грузового' || role === 'Попутный груз') {
                truckFields.classList.add('active');
                passengersInput.required = false;
                tonnageInput.required = true;
                volumeInput.required = true;
            }
        }

        function initAutocomplete(inputId) {
            const input = document.getElementById(inputId);
            let currentFocus = -1;
            
            input.addEventListener('input', function() {
                const value = this.value;
                closeAllLists();
                
                if (!value) return false;
                
                currentFocus = -1;
                
                const container = this.parentNode;
                const listDiv = document.createElement('div');
                listDiv.setAttribute('id', inputId + '-autocomplete-list');
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
                        input.value = city.fullName;
                        closeAllLists();
                    });
                    
                    listDiv.appendChild(itemDiv);
                });
            });
            
            input.addEventListener('keydown', function(e) {
                let list = document.getElementById(inputId + '-autocomplete-list');
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

        initAutocomplete('from');
        initAutocomplete('to');

        // Устанавливаем минимальную дату на сегодня
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date').setAttribute('min', today);
    </script>
</body>
</html> 