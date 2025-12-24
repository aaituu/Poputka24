<?php
include('db_connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Вы должны войти в систему, чтобы посмотерть заказ.');
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? null;

// Проверяем, был ли отправлен запрос на принятие заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_order'])) {
    $check_sql = "SELECT * FROM orders WHERE id = ? AND accepted_by IS NULL";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if ($order) {
        $update_sql = "UPDATE orders SET accepted_by = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ii', $user_id, $order_id);
        if ($stmt->execute()) {
            $success_message = "Заказ успешно принят!";
        } else {
            $error_message = "Ошибка при принятии заказа.";
        }
        $stmt->close();
    } else {
        $error_message = "Этот заказ уже принят или не существует.";
    }
}

// Получаем данные о заказе
$sql = "
    SELECT 
        orders.*, 
        users.phone AS creator_phone,
        users.username AS creator_name
    FROM 
        orders 
    LEFT JOIN 
        users 
    ON 
        orders.user_id = users.id 
    WHERE 
        orders.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Попутка 24 - Детали заказа</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
        
        .top-nav {
            margin-bottom: 30px;
        }
        
        .nav-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #38905dff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        
        .nav-button:hover {
            background-color: #2f784dff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .order-card {
            background: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }
        
        h1 {
            color: #34495e;
            margin-bottom: 25px;
            font-size: 2em;
            text-align: center;
            border-bottom: 3px solid #34495e;
            padding-bottom: 15px;
        }
        
        .order-section {
            margin: 25px 0;
        }
        
        .section-title {
            font-size: 1.3em;
            color: #34495e;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title::before {
            content: '';
            width: 4px;
            height: 24px;
            background: #34495e;
            border-radius: 2px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #34495e;
        }
        
        .info-label {
            font-size: 13px;
            color: #7f8c8d;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            color: #34495e;
            font-weight: 600;
        }
        
        .description-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #34495e;
            margin-top: 15px;
        }
        
        .description-box p {
            color: #2c3e50;
            line-height: 1.6;
            font-size: 15px;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
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
        
        .badge-accepted {
            background-color: #38905dff;
            color: white;
        }
        
        .action-section {
            background: #ecf0f1;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            margin-top: 30px;
        }
        
        .action-button {
            display: inline-block;
            padding: 15px 40px;
            background: #34495e;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .action-button:hover {
            background: #2c3c4dff;
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        
        .phone-link {
            display: inline-block;
            padding: 15px 40px;
            background: #38905dff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 18px;
            margin-top: 15px;
            transition: all 0.3s ease;
        }
        
        .phone-link:hover {
            background: #38905dff;
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .contact-info {
            background: linear-gradient(135deg, #34495e 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            margin-top: 20px;
        }
        
        .contact-info h3 {
            margin-bottom: 15px;
            font-size: 1.4em;
        }
        
        .creator-name {
            font-size: 1.2em;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .order-card {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.5em;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-nav">
            <a href="orders.php" class="nav-button">← Вернуться к списку</a>
        </div>

        <?php if ($order): ?>
           

            <div class="order-card">
                <h1>
                    Детали заказа
                    <span class="badge <?= $order['type'] === 'Грузовой' ? 'badge-truck' : 'badge-car' ?>">
                        <?= htmlspecialchars($order['type']); ?>
                    </span>
                    <?php if ($order['accepted_by']): ?>
                        <span class="badge badge-accepted">Открыт</span>
                    <?php endif; ?>
                </h1>

                <div class="order-section">
                    <div class="section-title">📍 Маршрут</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Откуда</div>
                            <div class="info-value"><?= htmlspecialchars($order['from_location']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Куда</div>
                            <div class="info-value"><?= htmlspecialchars($order['to_location']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Область</div>
                            <div class="info-value"><?= htmlspecialchars($order['region']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Дата поездки</div>
                            <div class="info-value"><?= date('d.m.Y', strtotime($order['date'])); ?></div>
                        </div>
                    </div>
                </div>

                <div class="order-section">
                    <div class="section-title">ℹ️ Информация о заказе</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Роль</div>
                            <div class="info-value"><?= htmlspecialchars($order['role']); ?></div>
                        </div>
                        
                        <?php if ($order['type'] === 'Легковой' && $order['passengers']): ?>
                            <div class="info-item">
                                <div class="info-label">👥 Количество пассажиров</div>
                                <div class="info-value"><?= $order['passengers']; ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($order['type'] === 'Грузовой'): ?>
                            <?php if ($order['tonnage']): ?>
                                <div class="info-item">
                                    <div class="info-label">⚖️ Тоннаж</div>
                                    <div class="info-value"><?= $order['tonnage']; ?> тонн</div>
                                </div>
                            <?php endif; ?>
                            <?php if ($order['volume']): ?>
                                <div class="info-item">
                                    <div class="info-label">📦 Объём</div>
                                    <div class="info-value"><?= $order['volume']; ?> м³</div>
                                </div>
                            <?php endif; ?>
                            <?php if ($order['cargo_type']): ?>
                                <div class="info-item">
                                    <div class="info-label">📋 Тип груза</div>
                                    <div class="info-value"><?= htmlspecialchars($order['cargo_type']); ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="description-box">
                        <div class="info-label">Описание</div>
                        <p><?= nl2br(htmlspecialchars($order['description'])); ?></p>
                    </div>
                </div>

                <?php if (!$order['accepted_by']): ?>
                    <div class="action-section">
                        <h3>Заинтересовал этот заказ?</h3>
                        <p style="margin: 15px 0; color: #7f8c8d;">Нажмите кнопку ниже, чтобы открыть контакты создателя</p>
                        <form method="POST">
                            <button type="submit" name="accept_order" class="action-button">
                                 Открыть контакты
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="contact-info">
                        <h3> Контактная информация</h3>
                        <div class="creator-name">Создатель: <?= htmlspecialchars($order['creator_name']); ?></div>
                        <a href="tel:<?= htmlspecialchars($order['creator_phone'] ?? ''); ?>" class="phone-link">
                            <?= htmlspecialchars($order['creator_phone'] ?? 'Нет данных'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="order-card">
                <h1>Заказ не найден</h1>
                <p style="text-align: center; color: #7f8c8d; margin-top: 20px;">
                    Возможно, заказ был удален или вы перешли по неправильной ссылке
                </p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>