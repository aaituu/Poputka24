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
    $type = $_POST['type'] ?? null;
    $region = $_POST['region'] ?? null;
    $from_location = $_POST['from'] ?? null;
    $from_lat = $_POST['from_lat'] ?? $order['from_lat'];
    $from_lng = $_POST['from_lng'] ?? $order['from_lng'];
    $to_location = $_POST['to'] ?? null;
    $to_lat = $_POST['to_lat'] ?? $order['to_lat'];
    $to_lng = $_POST['to_lng'] ?? $order['to_lng'];
    $date = $_POST['date'] ?? null;
    $description = $_POST['description'] ?? null;
    $role = $_POST['role'] ?? null;
    
    $passengers = ($type === 'Легковой') ? ($_POST['passengers'] ?? null) : null;
    $tonnage = ($type === 'Грузовой') ? ($_POST['tonnage'] ?? null) : null;
    $volume = ($type === 'Грузовой') ? ($_POST['volume'] ?? null) : null;
    $cargo_type = ($type === 'Грузовой') ? ($_POST['cargo_type'] ?? null) : null;

    if (!$type || !$region || !$from_location || !$to_location || !$date || !$role) {
        $error = "Ошибка: заполните все обязательные поля.";
    } else {
        $stmt = $conn->prepare("UPDATE orders SET type=?, region=?, from_location=?, from_lat=?, from_lng=?, 
                                to_location=?, to_lat=?, to_lng=?, date=?, description=?, role=?, 
                                passengers=?, tonnage=?, volume=?, cargo_type=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssddsddssiiddsii", $type, $region, $from_location, $from_lat, $from_lng, 
                          $to_location, $to_lat, $to_lng, $date, $description, $role, 
                          $passengers, $tonnage, $volume, $cargo_type, $order_id, $user_id);

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
    <link rel="stylesheet" href="/css/ordersCreate.css">
    <style>
        .autocomplete-container {
            position: relative;
            width: 100%;
        }
        
        .autocomplete-items {
            position: absolute;
            border: 1px solid #d4d4d4;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 200px;
            overflow-y: auto;
            background-color: white;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .autocomplete-items div {
            padding: 12px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #d4d4d4;
            color: #2c3e50;
            transition: background-color 0.2s;
        }
        
        .autocomplete-items div:hover {
            background-color: #e8f4f8;
        }
        
        .autocomplete-active {
            background-color: #3498db !important;
            color: white !important;
        }
        
        .city-name {
            font-weight: 600;
            font-size: 16px;
        }
        
        .city-region {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 2px;
        }
        
        .autocomplete-active .city-region {
            color: #ecf0f1;
        }
        
        .error-message {
            background-color: #e74c3c;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 20px auto;
            max-width: 600px;
            text-align: center;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .button-group button, .button-group a {
            flex: 1;
            max-width: 200px;
        }
        
        button.cancel-btn {
            background-color: #95a5a6;
        }
        
        button.cancel-btn:hover {
            background-color: #7f8c8d;
        }
    </style>
</head>
<body>
    <main>
        <h1>Редактирование заказа</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <form id="orderForm" action="editOrder.php?id=<?= $order_id ?>" method="POST">
            <label for="type">Тип перевозки:</label>
            <select name="type" id="type" required onchange="toggleFormFields()">
                <option value="Грузовой" <?= $order['type'] === 'Грузовой' ? 'selected' : '' ?>>Грузовой</option>
                <option value="Легковой" <?= $order['type'] === 'Легковой' ? 'selected' : '' ?>>Легковой</option>
            </select>
            <br>

            <label for="region">Область:</label>
            <select name="region" required>
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
                    $selected = ($order['region'] === $region) ? 'selected' : '';
                    echo "<option value='$region' $selected>$region</option>";
                }
                ?>
            </select>
            <br>

            <label for="from">Откуда:</label>
            <div class="autocomplete-container">
                <input type="text" name="from" id="from" value="<?= htmlspecialchars($order['from_location']) ?>" required autocomplete="off">
                <input type="hidden" name="from_lat" id="from_lat" value="<?= $order['from_lat'] ?>">
                <input type="hidden" name="from_lng" id="from_lng" value="<?= $order['from_lng'] ?>">
            </div>
            <br>

            <label for="to">Куда:</label>
            <div class="autocomplete-container">
                <input type="text" name="to" id="to" value="<?= htmlspecialchars($order['to_location']) ?>" required autocomplete="off">
                <input type="hidden" name="to_lat" id="to_lat" value="<?= $order['to_lat'] ?>">
                <input type="hidden" name="to_lng" id="to_lng" value="<?= $order['to_lng'] ?>">
            </div>
            <br>

            <label for="date">Дата:</label>
            <input type="date" name="date" value="<?= $order['date'] ?>" required>
            <br>

            <div id="carFields" style="display: <?= $order['type'] === 'Легковой' ? 'block' : 'none' ?>;">
                <label for="passengers">Количество пассажиров:</label>
                <input type="number" name="passengers" id="passengers" value="<?= $order['passengers'] ?>" min="1" max="20">
                <br>
            </div>

            <div id="truckFields" style="display: <?= $order['type'] === 'Грузовой' ? 'block' : 'none' ?>;">
                <label for="tonnage">Тоннаж (тонн):</label>
                <input type="number" name="tonnage" id="tonnage" value="<?= $order['tonnage'] ?>" step="0.1" min="0.1">
                <br>

                <label for="volume">Объём (м³):</label>
                <input type="number" name="volume" id="volume" value="<?= $order['volume'] ?>" step="0.1" min="0.1">
                <br>

                <label for="cargo_type">Тип груза:</label>
                <input type="text" name="cargo_type" id="cargo_type" value="<?= htmlspecialchars($order['cargo_type']) ?>">
                <br>
            </div>

            <label for="description">Описание:</label>
            <textarea name="description" required><?= htmlspecialchars($order['description']) ?></textarea>
            <br>

            <label for="role">Роль:</label>
            <select name="role" required>
                <option value="Попутчик" <?= $order['role'] === 'Попутчик' ? 'selected' : '' ?>>Попутчик</option>
                <option value="Водитель" <?= $order['role'] === 'Водитель' ? 'selected' : '' ?>>Водитель</option>
            </select>
            <br>

            <div class="button-group">
                <button type="submit" class="CreateBtn">💾 Сохранить</button>
                <a href="profile.php"><button type="button" class="cancel-btn">❌ Отмена</button></a>
            </div>
        </form>
    </main>

    <script>
        const cities = [
            {name: "Астана", region: "г. Астана", lat: 51.1694, lng: 71.4491},
            {name: "Алматы", region: "г. Алматы", lat: 43.2220, lng: 76.8512},
            {name: "Шымкент", region: "г. Шымкент", lat: 42.3000, lng: 69.5900},
            {name: "Кокшетау", region: "Акмолинская область", lat: 53.2872, lng: 69.3756},
            {name: "Актобе", region: "Актюбинская область", lat: 50.2839, lng: 57.1670},
            {name: "Талдыкорган", region: "Алматинская область", lat: 45.0150, lng: 78.3730},
            {name: "Атырау", region: "Атырауская область", lat: 47.1164, lng: 51.8830},
            {name: "Усть-Каменогорск", region: "Восточно-Казахстанская область", lat: 49.9787, lng: 82.6147},
            {name: "Семей", region: "Восточно-Казахстанская область", lat: 50.4111, lng: 80.2275},
            {name: "Тараз", region: "Жамбылская область", lat: 42.9000, lng: 71.3667},
            {name: "Уральск", region: "Западно-Казахстанская область", lat: 51.2167, lng: 51.3667},
            {name: "Караганда", region: "Карагандинская область", lat: 49.8047, lng: 73.1094},
            {name: "Костанай", region: "Костанайская область", lat: 53.2142, lng: 63.6246},
            {name: "Кызылорда", region: "Кызылординская область", lat: 44.8528, lng: 65.5094},
            {name: "Актау", region: "Мангистауская область", lat: 43.6500, lng: 51.2000},
            {name: "Павлодар", region: "Павлодарская область", lat: 52.2873, lng: 76.9674},
            {name: "Петропавловск", region: "Северо-Казахстанская область", lat: 54.8667, lng: 69.1500},
            {name: "Туркестан", region: "Туркестанская область", lat: 43.3000, lng: 68.2667}
        ];

        function toggleFormFields() {
            const type = document.getElementById('type').value;
            const carFields = document.getElementById('carFields');
            const truckFields = document.getElementById('truckFields');

            if (type === 'Легковой') {
                carFields.style.display = 'block';
                truckFields.style.display = 'none';
                document.getElementById('passengers').required = true;
                document.getElementById('tonnage').required = false;
                document.getElementById('volume').required = false;
            } else if (type === 'Грузовой') {
                carFields.style.display = 'none';
                truckFields.style.display = 'block';
                document.getElementById('passengers').required = false;
                document.getElementById('tonnage').required = true;
                document.getElementById('volume').required = true;
            }
        }

        function initAutocomplete(inputId, latId, lngId) {
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
                        <div class="city-region">${city.region}</div>
                    `;
                    
                    itemDiv.addEventListener('click', function() {
                        input.value = `${city.name}, ${city.region}`;
                        document.getElementById(latId).value = city.lat;
                        document.getElementById(lngId).value = city.lng;
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

        initAutocomplete('from', 'from_lat', 'from_lng');
        initAutocomplete('to', 'to_lat', 'to_lng');
        
        window.onload = function() {
            toggleFormFields();
        };
    </script>
</body>
</html>