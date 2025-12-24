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
            echo "<script>alert('Ошибка при обновлении заказа.');</script>";
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
    <script src="https://api-maps.yandex.ru/2.1/?apikey=YOUR_YANDEX_API_KEY&lang=ru_RU" type="text/javascript"></script>
</head>
<body>
    <main>
        <h1>Редактирование заказа</h1>

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

            <label>Откуда:</label>
            <input type="text" name="from" id="from" value="<?= htmlspecialchars($order['from_location']) ?>" required readonly>
            <input type="hidden" name="from_lat" id="from_lat" value="<?= $order['from_lat'] ?>">
            <input type="hidden" name="from_lng" id="from_lng" value="<?= $order['from_lng'] ?>">
            <button type="button" onclick="openMap('from')">Выбрать на карте</button>
            <br>

            <label>Куда:</label>
            <input type="text" name="to" id="to" value="<?= htmlspecialchars($order['to_location']) ?>" required readonly>
            <input type="hidden" name="to_lat" id="to_lat" value="<?= $order['to_lat'] ?>">
            <input type="hidden" name="to_lng" id="to_lng" value="<?= $order['to_lng'] ?>">
            <button type="button" onclick="openMap('to')">Выбрать на карте</button>
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

            <button type="submit" class="CreateBtn">Сохранить изменения</button>
            <a href="profile.php"><button type="button">Отмена</button></a>
        </form>

        <div id="mapModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000;">
            <div style="position: relative; width: 90%; height: 90%; margin: 2% auto; background: white; border-radius: 10px;">
                <button onclick="closeMap()" style="position: absolute; top: 10px; right: 10px; z-index: 1001;">Закрыть</button>
                <div id="map" style="width: 100%; height: 100%;"></div>
            </div>
        </div>
    </main>

    <script>
        let currentField = null;
        let myMap = null;

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

        function openMap(field) {
            currentField = field;
            document.getElementById('mapModal').style.display = 'block';
            
            if (!myMap) {
                ymaps.ready(initMap);
            }
        }

        function closeMap() {
            document.getElementById('mapModal').style.display = 'none';
        }

        function initMap() {
            myMap = new ymaps.Map("map", {
                center: [51.1694, 71.4491],
                zoom: 6,
                controls: ['zoomControl', 'searchControl']
            });

            myMap.events.add('click', function (e) {
                const coords = e.get('coords');
                
                ymaps.geocode(coords).then(function (res) {
                    const firstGeoObject = res.geoObjects.get(0);
                    const address = firstGeoObject.getAddressLine();
                    
                    document.getElementById(currentField).value = address;
                    document.getElementById(currentField + '_lat').value = coords[0];
                    document.getElementById(currentField + '_lng').value = coords[1];
                    
                    closeMap();
                });
            });
        }

        // Инициализация при загрузке
        window.onload = function() {
            toggleFormFields();
        };
    </script>
</body>
</html>