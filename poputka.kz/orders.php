<?php
// Подключаемся к базе данных
$conn = new mysqli('localhost', 'poputka_kz', 'plAEQeJRt77b2Da1', 'poputka_kz');
if ($conn->connect_error) {
    die('Ошибка подключения: ' . $conn->connect_error);
}

// Инициализируем сессию (если требуется авторизация)
session_start();
$user_id = $_SESSION['user_id'] ?? null; // ID текущего пользователя, если включена авторизация

// Если пользователь не авторизован, перенаправляем его на страницу входа
if ($user_id === null) {
    header("Location: login.php"); // Поменяйте на нужную страницу
    exit();
}

// Обработка запроса на удаление
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order_id'])) {
    $order_id = intval($_POST['delete_order_id']);

    // Проверяем, принадлежит ли заказ текущему пользователю
    $check_owner_sql = "SELECT user_id FROM orders WHERE id = $order_id";
    $check_owner_result = $conn->query($check_owner_sql);
    if ($check_owner_result->num_rows > 0) {
        $order = $check_owner_result->fetch_assoc();
        if ($order['user_id'] == $user_id) { // Проверка на принадлежность заказа текущему пользователю
            // Удаление заказа
            $delete_sql = "DELETE FROM orders WHERE id = $order_id";
            if ($conn->query($delete_sql)) {
                echo "Заказ успешно удален!";
            } else {
                echo "Ошибка удаления: " . $conn->error;
            }
        } else {
            echo "Вы не можете удалить чужой заказ.";
        }
    } else {
        echo "Заказ не найден.";
    }
}

// Фильтрация
$type_filter = $_GET['type'] ?? '';
$region_filter = $_GET['region'] ?? '';
$role_filter = $_GET['role'] ?? '';

$sql = "SELECT * FROM orders WHERE 1=1";

if ($type_filter) {
    $sql .= " AND type = '" . $conn->real_escape_string($type_filter) . "'";
}

if ($region_filter) {
    $sql .= " AND region = '" . $conn->real_escape_string($region_filter) . "'";
}

if ($role_filter) {
    $sql .= " AND role = '" . $conn->real_escape_string($role_filter) . "'";
}

$sql .= " ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список заказов</title>
    <link rel="stylesheet" href="/css/orders.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>
    <h1>Список заказов</h1>

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
            <option value="Акмолинская область" <?= $region_filter === 'Акмолинская область' ? 'selected' : '' ?>>Акмолинская область</option>

            <option value="Улытауская область" <?= $region_filter === 'Улытауская область' ? 'selected' : '' ?>>Улытауская область</option>
            <option value="Абайская область" <?= $region_filter === 'Абайская область' ? 'selected' : '' ?>>Абайская область</option>
            <option value="Жетысуйская область" <?= $region_filter === 'Жетысуйская область' ? 'selected' : '' ?>>Жетысуйская область</option>
            
            <option value="Актюбинская область" <?= $region_filter === 'Актюбинская область' ? 'selected' : '' ?>>Актюбинская область</option>
            <option value="Алматинская область" <?= $region_filter === 'Алматинская область' ? 'selected' : '' ?>>Алматинская область</option>
            <option value="Атырауская область" <?= $region_filter === 'Атырауская область' ? 'selected' : '' ?>>Атырауская область</option>
            <option value="Восточно-Казахстанская область" <?= $region_filter === 'Восточно-Казахстанская область' ? 'selected' : '' ?>>Восточно-Казахстанская область</option>
            <option value="Жамбылская область" <?= $region_filter === 'Жамбылская область' ? 'selected' : '' ?>>Жамбылская область</option>
            <option value="Западно-Казахстанская область" <?= $region_filter === 'Западно-Казахстанская область' ? 'selected' : '' ?>>Западно-Казахстанская область</option>
            <option value="Карагандинская область" <?= $region_filter === 'Карагандинская область' ? 'selected' : '' ?>>Карагандинская область</option>
            <option value="Костанайская область" <?= $region_filter === 'Костанайская область' ? 'selected' : '' ?>>Костанайская область</option>
            <option value="Кызылординская область" <?= $region_filter === 'Кызылординская область' ? 'selected' : '' ?>>Кызылординская область</option>
            <option value="Мангистауская область" <?= $region_filter === 'Мангистауская область' ? 'selected' : '' ?>>Мангистауская область</option>
            <option value="Павлодарская область" <?= $region_filter === 'Павлодарская область' ? 'selected' : '' ?>>Павлодарская область</option>
            <option value="Северо-Казахстанская область" <?= $region_filter === 'Северо-Казахстанская область' ? 'selected' : '' ?>>Северо-Казахстанская область</option>
            <option value="Туркестанская область" <?= $region_filter === 'Туркестанская область' ? 'selected' : '' ?>>Туркестанская область</option>
            <option value="город Алматы" <?= $region_filter === 'город Алматы' ? 'selected' : '' ?>>город Алматы</option>
            <option value="город Нур-Султан" <?= $region_filter === 'город Нур-Султан' ? 'selected' : '' ?>>город Нур-Султан</option>
            <option value="город Шымкент" <?= $region_filter === 'город Шымкент' ? 'selected' : '' ?>>город Шымкент</option>
        </select>

        <label for="role">Кого ищете?:</label>
        <select name="role" id="role">
            <option value="">Все</option>
            <option value="Попутчик" <?= $role_filter === 'Попутчик' ? 'selected' : '' ?>>Попутчик</option>
            <option value="Водитель" <?= $role_filter === 'Водитель' ? 'selected' : '' ?>>Водитель</option>
        </select>

        <button type="submit">Применить фильтр</button>
    </form>

    <ul>
        <?php
        if ($result->num_rows > 0) {
            while ($order = $result->fetch_assoc()) {
                // Проверяем наличие всех нужных полей
                $type = $order['type'] ?? 'Не указан';
                $region = $order['region'] ?? 'Не указана';
                $role = $order['role'] ?? 'Не указана';
                $from_location = $order['from_location'] ?? 'Не указано';
                $to_location = $order['to_location'] ?? 'Не указано';
                $description = $order['description'] ?? 'Без описания';
                $order_owner_id = $order['user_id']; // ID владельца заказа

                echo "<li>
                        <a href='/orderDetails.php?id={$order['id']}'>
                            <strong>Тип:</strong> $type <br>
                            <strong>Область:</strong> $region<br>
                            <strong>Роль:</strong> $role <br>
                            <strong>Откуда:</strong> $from_location <br>
                            <strong>Куда:</strong> $to_location <br>
                            <strong>Описание:</strong> $description<br>
                        </a>";

                // Показываем кнопку удаления только если текущий пользователь является владельцем заказа
                if ($order_owner_id == $user_id) {
                    echo "
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='delete_order_id' value='{$order['id']}'>
                            <button type='submit' onclick='return confirm(\"Вы уверены, что хотите удалить этот заказ?\")'>Удалить</button>
                        </form>
                    ";
                }

                echo "</li>";
            }
        } else {
            echo "<li>Нет заказов.</li>";
        }
        ?>
    </ul><br>
    <a href="index.php"><button>На Главную</button></a>

</body>
</html>

<?php
$conn->close();
?>
