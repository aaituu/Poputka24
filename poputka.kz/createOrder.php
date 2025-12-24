<?php
include('db_connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? null;
    $region = $_POST['region'] ?? null;
    $from_location = $_POST['from'] ?? null;
    $from_lat = $_POST['from_lat'] ?? null;
    $from_lng = $_POST['from_lng'] ?? null;
    $to_location = $_POST['to'] ?? null;
    $to_lat = $_POST['to_lat'] ?? null;
    $to_lng = $_POST['to_lng'] ?? null;
    $date = $_POST['date'] ?? null;
    $description = $_POST['description'] ?? null;
    $role = $_POST['role'] ?? null;
    
    $passengers = ($type === 'Легковой') ? ($_POST['passengers'] ?? null) : null;
    $tonnage = ($type === 'Грузовой') ? ($_POST['tonnage'] ?? null) : null;
    $volume = ($type === 'Грузовой') ? ($_POST['volume'] ?? null) : null;
    $cargo_type = ($type === 'Грузовой') ? ($_POST['cargo_type'] ?? null) : null;

    if (!$type || !$region || !$from_location || !$to_location || !$date || !$role) {
        echo "<script>alert('Ошибка: заполните все обязательные поля.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO orders (user_id, type, region, from_location, from_lat, from_lng, 
                                to_location, to_lat, to_lng, date, description, role, passengers, tonnage, volume, cargo_type) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssddsddssiidds", $user_id, $type, $region, $from_location, $from_lat, $from_lng, 
                          $to_location, $to_lat, $to_lng, $date, $description, $role, $passengers, $tonnage, $volume, $cargo_type);

        if ($stmt->execute()) {
            header("Location: orders.php");
            exit();
        } else {
            echo "<script>alert('Ошибка при создании заказа.');</script>";
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
    <title>Попутка 24 - Создание заказа</title>
    <link rel="stylesheet" href="/css/ordersCreate.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { 
            height: 100%; 
            width: 100%;
            border-radius: 10px;
        }
        #mapModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
        }
        #mapModal .modal-content {
            position: relative;
            width: 90%;
            height: 90%;
            margin: 2% auto;
            background: white;
            border-radius: 10px;
            padding: 10px;
        }
        #mapModal .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1001;
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        #mapModal .close-btn:hover {
            background: #c0392b;
        }
        .map-select-btn {
            background-color: #27ae60;
            margin-top: 5px;
            width: 100%;
        }
        .map-select-btn:hover {
            background-color: #229954;
        }
    </style>
</head>
<body>
    <main>
        <h1>Создание заказа</h1>

        <form id="orderForm" action="createOrder.php" method="POST">
            <label for="type">Тип перевозки:</label>
            <select name="type" id="type" required onchange="toggleFormFields()">
                <option value="">Выберите тип</option>
                <option value="Грузовой">Грузовой</option>
                <option value="Легковой">Легковой</option>
            </select>
            <br>

            <label for="region">Область:</label>
            <select name="region" required>
                <option value="">Выберите область</option>
                <option value="Акмолинская область">Акмолинская область</option>
                <option value="Улытауская область">Улытауская область</option>
                <option value="Абайская область">Абайская область</option>
                <option value="Жетысуйская область">Жетысуйская область</option>
                <option value="Актюбинская область">Актюбинская область</option>
                <option value="Алматинская область">Алматинская область</option>
                <option value="Атырауская область">Атырауская область</option>
                <option value="Восточно-Казахстанская область">Восточно-Казахстанская область</option>
                <option value="Жамбылская область">Жамбылская область</option>
                <option value="Западно-Казахстанская область">Западно-Казахстанская область</option>
                <option value="Карагандинская область">Карагандинская область</option>
                <option value="Костанайская область">Костанайская область</option>
                <option value="Кызылординская область">Кызылординская область</option>
                <option value="Мангистауская область">Мангистауская область</option>
                <option value="Павлодарская область">Павлодарская область</option>
                <option value="Северо-Казахстанская область">Северо-Казахстанская область</option>
                <option value="Туркестанская область">Туркестанская область</option>
            </select>
            <br>

            <label>Откуда:</label>
            <input type="text" name="from" id="from" required readonly placeholder="Нажмите кнопку ниже">
            <input type="hidden" name="from_lat" id="from_lat">
            <input type="hidden" name="from_lng" id="from_lng">
            <button type="button" class="map-select-btn" onclick="openMap('from')">🗺️ Выбрать на карте</button>
            <br>

            <label>Куда:</label>
            <input type="text" name="to" id="to" required readonly placeholder="Нажмите кнопку ниже">
            <input type="hidden" name="to_lat" id="to_lat">
            <input type="hidden" name="to_lng" id="to_lng">
            <button type="button" class="map-select-btn" onclick="openMap('to')">🗺️ Выбрать на карте</button>
            <br>

            <label for="date">Дата:</label>
            <input type="date" name="date" required>
            <br>

            <div id="carFields" style="display: none;">
                <label for="passengers">Количество пассажиров:</label>
                <input type="number" name="passengers" id="passengers" min="1" max="20">
                <br>
            </div>

            <div id="truckFields" style="display: none;">
                <label for="tonnage">Тоннаж (тонн):</label>
                <input type="number" name="tonnage" id="tonnage" step="0.1" min="0.1">
                <br>

                <label for="volume">Объём (м³):</label>
                <input type="number" name="volume" id="volume" step="0.1" min="0.1">
                <br>

                <label for="cargo_type">Тип груза:</label>
                <input type="text" name="cargo_type" id="cargo_type" placeholder="Например: стройматериалы">
                <br>
            </div>

            <label for="description">Описание:</label>
            <textarea name="description" required></textarea>
            <br>

            <label for="role">Роль:</label>
            <select name="role" required>
                <option value="Попутчик">Попутчик</option>
                <option value="Водитель">Водитель</option>
            </select>
            <br>

            <button type="submit" class="CreateBtn">Создать заказ</button>
        </form>

        <div id="mapModal">
            <div class="modal-content">
                <button class="close-btn" onclick="closeMap()">✖ Закрыть</button>
                <div id="map"></div>
            </div>
        </div>
    </main>

    <script>
        let currentField = null;
        let myMap = null;
        let marker = null;

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
            } else {
                carFields.style.display = 'none';
                truckFields.style.display = 'none';
            }
        }

        function openMap(field) {
            currentField = field;
            document.getElementById('mapModal').style.display = 'block';
            
            if (!myMap) {
                setTimeout(initMap, 100);
            }
        }

        function closeMap() {
            document.getElementById('mapModal').style.display = 'none';
        }

        function initMap() {
            myMap = L.map('map').setView([48.0196, 66.9237], 6); // Центр Казахстана

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(myMap);

            myMap.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                
                // Удаляем предыдущий маркер
                if (marker) {
                    myMap.removeLayer(marker);
                }
                
                // Добавляем новый маркер
                marker = L.marker([lat, lng]).addTo(myMap);
                
                // Получаем адрес через Nominatim (OpenStreetMap)
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ru`)
                    .then(response => response.json())
                    .then(data => {
                        const address = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        
                        document.getElementById(currentField).value = address;
                        document.getElementById(currentField + '_lat').value = lat;
                        document.getElementById(currentField + '_lng').value = lng;
                        
                        marker.bindPopup(address).openPopup();
                        
                        setTimeout(closeMap, 2000);
                    })
                    .catch(error => {
                        const coords = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        document.getElementById(currentField).value = coords;
                        document.getElementById(currentField + '_lat').value = lat;
                        document.getElementById(currentField + '_lng').value = lng;
                        
                        setTimeout(closeMap, 1000);
                    });
            });
        }
    </script>
</body>
</html>