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
        $error = "Ошибка: заполните все обязательные поля.";
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
            $error = "Ошибка при создании заказа.";
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
        
        input[type="text"]:focus {
            border: 2px solid #3498db;
            outline: none;
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
    </style>
</head>
<body>
    <main>
        <h1>Создание заказа</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

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

            <label for="from">Откуда:</label>
            <div class="autocomplete-container">
                <input type="text" name="from" id="from" required autocomplete="off" placeholder="Начните вводить название города...">
                <input type="hidden" name="from_lat" id="from_lat">
                <input type="hidden" name="from_lng" id="from_lng">
            </div>
            <br>

            <label for="to">Куда:</label>
            <div class="autocomplete-container">
                <input type="text" name="to" id="to" required autocomplete="off" placeholder="Начните вводить название города...">
                <input type="hidden" name="to_lat" id="to_lat">
                <input type="hidden" name="to_lng" id="to_lng">
            </div>
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
            <textarea name="description" required placeholder="Опишите детали поездки..."></textarea>
            <br>

            <label for="role">Роль:</label>
            <select name="role" required>
                <option value="Попутчик">Попутчик</option>
                <option value="Водитель">Водитель</option>
            </select>
            <br>

            <button type="submit" class="CreateBtn">Создать заказ</button>
            <a href="index.php"><button type="button">Отмена</button></a>
        </form>
    </main>

    <script>
        // База городов Казахстана
    

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
                
                if (e.keyCode === 40) { // DOWN
                    currentFocus++;
                    addActive(list);
                    e.preventDefault();
                } else if (e.keyCode === 38) { // UP
                    currentFocus--;
                    addActive(list);
                    e.preventDefault();
                } else if (e.keyCode === 13) { // ENTER
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
    fetch('/cities.json') // ← путь поменяй, если файл лежит в другом месте
    .then(response => {
        if (!response.ok) {
            throw new Error('Ошибка загрузки cities.json');
        }
        return response.json();
    })
    .then(data => {
        cities = data;
        console.log('Города загружены:', cities.length);
        
        // Инициализация после загрузки
        initAutocomplete('from', 'from_lat', 'from_lng');
        initAutocomplete('to', 'to_lat', 'to_lng');
    })
    .catch(error => {
        console.error(error);
    });

  
    </script>
</body>
</html>