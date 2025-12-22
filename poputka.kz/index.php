<?php
// Начинаем сессию
session_start();

// Проверяем, была ли отправлена форма для выхода
if (isset($_POST['logout'])) {
    // Удаляем все данные сессии
    session_unset();
    // Уничтожаем сессию
    session_destroy();
    // Перенаправляем на главную страницу
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="keywords" content="Poputka.kz, попутка, полпутчик, poputki, сайт грузоперевозок, пассажирские перевозки, найти попутку">
    <title>Poputka</title>
    <link rel="stylesheet" href="/css/style.css" />
    <link rel="icon" href="/favicon.ico" type="image/ico">
    <meta name="description" content="Попутные Пассажирские и Грузовые перевозки по Казахстану и ближнему зарубежью">
  </head>
  <body>
    <header>
      <div class="logo">
        <h1>Poputka</h1>
      </div>
      <nav>
            <a href="/profile.php">Профиль</a>
      </nav>
    </header>

    <section class="hero-section">
      <div class="hero-content">
        <!-- Если пользователь авторизован, показываем кнопку для выхода -->
        <?php if (isset($_SESSION['username'])): ?>
          <form method="POST">
            <button type="submit" name="logout" class="ExitBtn">Выйти</button>
          </form>
        <?php else: ?>
          <!-- Если пользователь не авторизован, показываем кнопки для входа и регистрации -->
          <a href="/login.php"><button class="tak">Авторизация</button></a>
          <a href="/register.php"><button>Регистрация</button></a>
        <?php endif; ?>

        <h1>Добро пожаловать в Poputka!<?php
          if (isset($_SESSION['username'])) {
              echo " " . htmlspecialchars($_SESSION['username']);
          } else {
              echo "Привет, гость!";
          }
        ?></h1>
        <p>Пассажирские и грузовые перевозки</p>
        <a href="/createOrder.php"><button class="CreateBtn">Создать заказ</button></a><br><br>
        <a href="/orders.php"><button>Посмотреть заказы</button></a>
      </div>
    </section>

    <section class="bottom">
      <h1>
        У вас есть идеи для улучшения приложения?<br />
        Или вам встретился недобросовестный пользователь?<br>
        <a href="mailto:Poputka.kz@list.ru?subject=Тема письма&body=Текст письма">
          Написать нам на почту Poputka.kz@list.ru
        </a>
      </h1>
    </section>
  </body>
</html>
