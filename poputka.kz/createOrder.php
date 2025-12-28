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
    $from_location = $_POST['from'] ?? null;
    $to_location = $_POST['to'] ?? null;
    $date = $_POST['date'] ?? null;
    $description = $_POST['description'] ?? null;
    $role = $_POST['role'] ?? null;
    
    $from_lat = $_POST['from_lat'] ?? null;
    $from_lng = $_POST['from_lng'] ?? null;
    $to_lat = $_POST['to_lat'] ?? null;
    $to_lng = $_POST['to_lng'] ?? null;
    
    $passengers = ($role === 'Водитель легкового' || $role === 'Попутчик') ? ($_POST['passengers'] ?? null) : null;
    $tonnage = ($role === 'Водитель грузового' || $role === 'Попутный груз') ? ($_POST['tonnage'] ?? null) : null;
    $volume = ($role === 'Водитель грузового' || $role === 'Попутный груз') ? ($_POST['volume'] ?? null) : null;

    if (!$type || !$from_location || !$to_location || !$date || !$role) {
        $error = "Ошибка: заполните все обязательные поля.";
    } else {
        $from_parts = explode(', ', $from_location);
        $region = isset($from_parts[1]) ? $from_parts[1] : (isset($from_parts[0]) ? $from_parts[0] : '');
        
        $stmt = $conn->prepare("INSERT INTO orders (user_id, type, region, from_location, from_lat, from_lng, to_location, to_lat, to_lng, date, description, role, passengers, tonnage, volume) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssddsddsssidd", $user_id, $type, $region, $from_location, $from_lat, $from_lng, $to_location, $to_lat, $to_lng, $date, $description, $role, $passengers, $tonnage, $volume);

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
            max-height: 300px;
            overflow-y: auto;
            background-color: white;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
        
        .location-name {
            font-weight: 600;
            font-size: 15px;
        }
        
        .location-details {
            font-size: 13px;
            color: #7f8c8d;
            margin-top: 3px;
        }
        
        .autocomplete-active .location-details {
            color: #ecf0f1;
        }
        
        .loading-indicator {
            padding: 12px 15px;
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .no-results {
            padding: 12px 15px;
            text-align: center;
            color: #95a5a6;
            font-size: 14px;
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
            background-color: #27ae60;
            color: white;
        }
        
        .btn-submit:hover {
            background-color: #229954;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
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
        
        .hint-text {
            font-size: 13px;
            color: #7f8c8d;
            margin-top: 5px;
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
        <h1>Создание заказа</h1>
        
        <div class="form-card">
            <?php if (isset($error)): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>

            <form id="orderForm" action="createOrder.php" method="POST">
                <div class="form-group">
                    <label for="role">Я:</label>
                    <select name="role" id="role" required onchange="updateForm()">
                        <option value="">Выберите роль</option>
                        <option value="Водитель легкового">Водитель легкового</option>
                        <option value="Водитель грузового">Водитель грузового</option>
                        <option value="Попутчик">Попутчик</option>
                        <option value="Попутный груз">Попутный груз</option>
                    </select>
                </div>

                <input type="hidden" name="type" id="type">

                <div class="form-group">
                    <label for="from">Откуда:</label>
                    <div class="autocomplete-container">
                        <input type="text" name="from" id="from" required autocomplete="off" placeholder="Начните вводить: город, посёлок, село...">
                        <input type="hidden" name="from_lat" id="from_lat">
                        <input type="hidden" name="from_lng" id="from_lng">
                    </div>
                    <div class="hint-text">🌍 Поиск по всему миру: любой город, посёлок или село</div>
                </div>

                <div class="form-group">
                    <label for="to">Куда:</label>
                    <div class="autocomplete-container">
                        <input type="text" name="to" id="to" required autocomplete="off" placeholder="Начните вводить: город, посёлок, село...">
                        <input type="hidden" name="to_lat" id="to_lat">
                        <input type="hidden" name="to_lng" id="to_lng">
                    </div>
                    <div class="hint-text">🌍 Поиск по всему миру: любой город, посёлок или село</div>
                </div>

                <div class="form-group">
                    <label for="date">Дата:</label>
                    <input type="date" name="date" id="date" required>
                </div>

                <div id="carFields" class="dynamic-fields">
                    <div class="form-group">
                        <label for="passengers">Количество мест:</label>
                        <input type="number" name="passengers" id="passengers" min="1" max="20" placeholder="Например: 3">
                    </div>
                </div>

                <div id="truckFields" class="dynamic-fields">
                    <div class="form-group">
                        <label for="tonnage">Грузоподъёмность (тонн):</label>
                        <input type="number" name="tonnage" id="tonnage" step="0.1" min="0.1" placeholder="Например: 5">
                    </div>

                    <div class="form-group">
                        <label for="volume">Объём кузова (м³):</label>
                        <input type="number" name="volume" id="volume" step="0.1" min="0.1" placeholder="Например: 20">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Описание:</label>
                    <textarea name="description" id="description" required placeholder="Укажите детали поездки: время отправления, особые условия и т.д."></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-submit">✓ Создать заказ</button>
                    <a href="index.php" style="flex: 1; text-decoration: none;">
                        <button type="button" class="btn-cancel" style="width: 100%;">✕ Отмена</button>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Улучшенная функция поиска с несколькими стратегиями
        async function searchPlaces(query) {
            if (query.length < 2) return [];
            
            try {
                // Стратегия 1: Поиск с подстановочным символом (более широкий поиск)
                const wildcardSearch = fetch(
                    `https://nominatim.openstreetmap.org/search?` +
                    `q=${encodeURIComponent(query + '*')}&` +
                    `format=json&` +
                    `addressdetails=1&` +
                    `limit=20&` +
                    `accept-language=ru`
                );
                
                // Стратегия 2: Точный поиск по началу названия
                const exactSearch = fetch(
                    `https://nominatim.openstreetmap.org/search?` +
                    `q=${encodeURIComponent(query)}&` +
                    `format=json&` +
                    `addressdetails=1&` +
                    `limit=20&` +
                    `accept-language=ru`
                );
                
                // Выполняем оба запроса параллельно
                const [wildcardResponse, exactResponse] = await Promise.all([
                    wildcardSearch,
                    exactSearch
                ]);
                
                if (!wildcardResponse.ok || !exactResponse.ok) {
                    throw new Error('Search failed');
                }
                
                const [wildcardData, exactData] = await Promise.all([
                    wildcardResponse.json(),
                    exactResponse.json()
                ]);
                
                // Объединяем результаты
                const allData = [...wildcardData, ...exactData];
                
                // Удаляем дубликаты по place_id
                const uniqueData = Array.from(
                    new Map(allData.map(item => [item.place_id, item])).values()
                );
                
                // Фильтруем и форматируем результаты
                const results = uniqueData
                    .filter(place => {
                        const types = ['city', 'town', 'village', 'hamlet', 'suburb', 'municipality', 'administrative'];
                        const name = place.name || '';
                        const lowerQuery = query.toLowerCase();
                        const lowerName = name.toLowerCase();
                        
                        // Показываем только населённые пункты и те, что начинаются с поискового запроса
                        return (types.includes(place.type) || place.class === 'place') &&
                               lowerName.startsWith(lowerQuery);
                    })
                    .map(place => {
                        const address = place.address || {};
                        const parts = [];
                        
                        const name = place.name || 
                                   address.city || 
                                   address.town || 
                                   address.village || 
                                   address.hamlet ||
                                   address.municipality;
                        
                        const region = address.state || address.region || address.county;
                        const country = address.country;
                        
                        if (name) parts.push(name);
                        if (region) parts.push(region);
                        if (country) parts.push(country);
                        
                        return {
                            name: name,
                            fullName: parts.join(', '),
                            displayName: place.display_name,
                            lat: place.lat,
                            lng: place.lon,
                            type: place.type,
                            region: region,
                            country: country,
                            importance: place.importance || 0
                        };
                    })
                    .filter(place => place.name);
                
                // Сортируем по важности и алфавиту
                results.sort((a, b) => {
                    // Сначала по длине названия (короткие - выше)
                    const lenDiff = a.name.length - b.name.length;
                    if (Math.abs(lenDiff) > 3) return lenDiff;
                    
                    // Потом по важности (importance)
                    const impDiff = b.importance - a.importance;
                    if (Math.abs(impDiff) > 0.1) return impDiff;
                    
                    // Потом по алфавиту
                    return a.name.localeCompare(b.name, 'ru');
                });
                
                // Ограничиваем до 15 результатов
                return results.slice(0, 15);
                    
            } catch (error) {
                console.error('Search error:', error);
                return [];
            }
        }

        function updateForm() {
            const role = document.getElementById('role').value;
            const typeInput = document.getElementById('type');
            const carFields = document.getElementById('carFields');
            const truckFields = document.getElementById('truckFields');
            const passengersInput = document.getElementById('passengers');
            const tonnageInput = document.getElementById('tonnage');
            const volumeInput = document.getElementById('volume');

            carFields.classList.remove('active');
            truckFields.classList.remove('active');

            if (role === 'Водитель легкового' || role === 'Попутчик') {
                typeInput.value = 'Легковой';
                carFields.classList.add('active');
                passengersInput.required = true;
                tonnageInput.required = false;
                volumeInput.required = false;
            } else if (role === 'Водитель грузового' || role === 'Попутный груз') {
                typeInput.value = 'Грузовой';
                truckFields.classList.add('active');
                passengersInput.required = false;
                tonnageInput.required = true;
                volumeInput.required = true;
            } else {
                typeInput.value = '';
                passengersInput.required = false;
                tonnageInput.required = false;
                volumeInput.required = false;
            }
        }

        function initAutocomplete(inputId, latId, lngId) {
            const input = document.getElementById(inputId);
            let currentFocus = -1;
            
            const debouncedSearch = debounce(async (value) => {
                closeAllLists();
                
                if (!value || value.length < 2) return;
                
                currentFocus = -1;
                
                const container = input.parentNode;
                const listDiv = document.createElement('div');
                listDiv.setAttribute('id', inputId + '-autocomplete-list');
                listDiv.setAttribute('class', 'autocomplete-items');
                container.appendChild(listDiv);
                
                listDiv.innerHTML = '<div class="loading-indicator">🔍 Поиск...</div>';
                
                const results = await searchPlaces(value);
                
                listDiv.innerHTML = '';
                
                if (results.length === 0) {
                    listDiv.innerHTML = '<div class="no-results">Ничего не найдено. Попробуйте другой запрос.</div>';
                    return;
                }
                
                results.forEach(place => {
                    const itemDiv = document.createElement('div');
                    
                    let typeIcon = '📍';
                    if (place.type === 'city') typeIcon = '🏙️';
                    else if (place.type === 'town') typeIcon = '🏘️';
                    else if (place.type === 'village') typeIcon = '🏡';
                    
                    itemDiv.innerHTML = `
                        <div class="location-name">${typeIcon} ${place.name}</div>
                        <div class="location-details">${place.region ? place.region + ', ' : ''}${place.country}</div>
                    `;
                    
                    itemDiv.addEventListener('click', function() {
                        input.value = place.fullName;
                        document.getElementById(latId).value = place.lat;
                        document.getElementById(lngId).value = place.lng;
                        closeAllLists();
                    });
                    
                    listDiv.appendChild(itemDiv);
                });
            }, 300); // Уменьшил до 300ms для более быстрого отклика
            
            input.addEventListener('input', function() {
                debouncedSearch(this.value);
            });
            
            input.addEventListener('keydown', function(e) {
                let list = document.getElementById(inputId + '-autocomplete-list');
                if (list) {
                    let items = list.getElementsByTagName('div');
                    items = Array.from(items).filter(item => 
                        !item.classList.contains('loading-indicator') && 
                        !item.classList.contains('no-results')
                    );
                    
                    if (e.keyCode === 40) {
                        currentFocus++;
                        addActive(items);
                        e.preventDefault();
                    } else if (e.keyCode === 38) {
                        currentFocus--;
                        addActive(items);
                        e.preventDefault();
                    } else if (e.keyCode === 13) {
                        e.preventDefault();
                        if (currentFocus > -1 && items[currentFocus]) {
                            items[currentFocus].click();
                        }
                    }
                }
            });
            
            function addActive(items) {
                if (!items || items.length === 0) return false;
                removeActive(items);
                if (currentFocus >= items.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = items.length - 1;
                items[currentFocus].classList.add('autocomplete-active');
            }
            
            function removeActive(items) {
                for (let i = 0; i < items.length; i++) {
                    items[i].classList.remove('autocomplete-active');
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

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date').setAttribute('min', today);
    </script>
</body>
</html>