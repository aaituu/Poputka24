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
    <meta name="google-site-verification" content="C5bhZvmbzgtvYVGv8tE6-ioDbijcasfaNDv6rUoknzs" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="keywords" content="Poputka.kz, попутка, попутчик, poputki, сайт грузоперевозок, пассажирские перевозки, найти попутку">
    <title>Попутка24 - поиск попутчиков и поездок</title>
    <link rel="stylesheet" href="/css/style.css" />
    <link rel="icon" href="/favicon.ico" >
    <meta name="description" content="Попутка 24 - Попутные Пассажирские и Грузовые перевозки по Казахстану и ближнему зарубежью">
  </head>
  <body>
    <header>
      <div class="logo">
        <h1>Попутка 24</h1>
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

        <h1>Добро пожаловать в Попутка 24!<?php
          if (isset($_SESSION['username'])) {
              echo " " . htmlspecialchars($_SESSION['username']);
          }
        ?></h1>
        <p>Пассажирские и грузовые перевозки</p>
        <a href="/createOrder.php"><button class="CreateBtn">Создать заказ</button></a><br><br>
        <a href="/orders.php"><button>Посмотреть заказы</button></a>
      </div>
    </section>

    <section class="bottom">
      <h1>
        Ищем инвестора для дальнейшей реализации проекта<br />
        <a href="mailto:Hosting_R@outlook.com?subject=Инвестиции в Попутка 24&body=Здравствуйте!">
          Написать нам на почту Hosting_R@outlook.com
        </a>
      </h1>
    </section>   
  </body>
</html>

