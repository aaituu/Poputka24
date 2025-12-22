<!-- chat.ejs -->
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат</title>
    <script src="/socket.io/socket.io.js"></script>
    <script>
        const socket = io(); // Подключаемся к серверу

        const chatId = '<%= chat._id %>'; // Получаем ID чата из шаблона
        socket.emit('joinChat', chatId); // Присоединяемся к чату

        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value;

            socket.emit('sendMessage', { chatId, message }); // Отправляем сообщение
            messageInput.value = ''; // Очищаем поле ввода
        }

        socket.on('receiveMessage', (message) => {
            const messagesContainer = document.getElementById('messages');
            const messageElement = document.createElement('div');
            messageElement.textContent = message; // Добавляем сообщение в контейнер
            messagesContainer.appendChild(messageElement);
        });
    </script>
</head>
<body>
    <h1>Чат</h1>
    <div id="messages"></div>
    <input id="messageInput" type="text" placeholder="Введите сообщение" />
    <button onclick="sendMessage()">Отправить</button>
</body>
</html>
