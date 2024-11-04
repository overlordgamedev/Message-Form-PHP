<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма для отправки сообщения</title>
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <link rel="stylesheet" type="text/css" href="css/anim_bg.css">
    <script src="js/gsap.min.js"></script>
</head>

<body>
    <canvas id="demo-canvas"></canvas>
    <script src="js/anim_bg.js"></script>

    <h2>Отправьте свое сообщение</h2>

    <form class="main_forma" action="" method="post" onsubmit="return validateCaptcha(event);">
        <div class="main_block"> 
            <label for="email">Почта:</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username" required><br><br>

            <label for="message">Сообщение:</label>
            <textarea id="message" name="message" required></textarea><br><br>

            <!-- Капча -->
            <label for="captcha">Введите код с картинки:</label><br>
            <canvas id="captchaCanvas" width="100" height="40"></canvas><br>
            <input type="text" id="captcha-input" name="captcha" required>
            <input type="hidden" id="captcha-answer"><br><br> <!-- Скрытый правильный ответ капчи -->

            <!-- Скрытые поля для IP и браузера -->
            <input type="hidden" id="user-ip" name="user_ip">
            <input type="hidden" id="user-browser" name="user_browser">

            <button type="submit" name="submit">Отправить</button>
        </div>
    </form>

    <?php
     if (isset($_POST['submit'])) {
         include 'db.php'; // Подключаемся к базе данных

         // Получаем данные из формы
         $email = $_POST['email'];
         $username = $_POST['username'];
         $message = $_POST['message'];
         $user_ip = $_POST['user_ip'];
         $user_browser = $_POST['user_browser'];

         // SQL-запрос на вставку данных
         $sql = "INSERT INTO messages (email, username, message, user_ip, user_browser) VALUES (:email, :username, :message, :user_ip, :user_browser)";
         $stmt = $pdo->prepare($sql);

         // Выполнение запроса с передачей параметров
         $stmt->execute([
             ':email' => $email,
             ':username' => $username,
             ':message' => $message,
             ':user_ip' => $user_ip,
             ':user_browser' => $user_browser
         ]);

         echo "<p>Сообщение успешно отправлено!</p>";
     }
    ?>

    <script>
        // Функция для генерации случайного текста капчи
        function generateCaptcha() {
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let captchaText = '';
            for (let i = 0; i < 6; i++) {
                captchaText += characters.charAt(Math.floor(Math.random() * characters.length));
            }
            // Отображаем капчу на Canvas
            const canvas = document.getElementById('captchaCanvas');
            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height); // Очищаем предыдущую капчу
            context.font = '20px Arial';
            context.fillStyle = '#000000';
            context.fillText(captchaText, 10, 30);

            // Сохраняем текст капчи в скрытом поле для проверки
            document.getElementById('captcha-answer').value = captchaText;
        }

        // Проверка капчи перед отправкой формы
        function validateCaptcha(event) {
            const userAnswer = document.getElementById('captcha-input').value;
            const correctAnswer = document.getElementById('captcha-answer').value;
            if (userAnswer !== correctAnswer) {
                event.preventDefault(); // Отменяем отправку формы, если ответ неверный
                alert("Капча введена неверно. Попробуйте снова.");
                generateCaptcha(); // Генерируем новую капчу
                return false;
            }
            return true;
        }

        // Запись информации о браузере
        document.getElementById('user-browser').value = navigator.userAgent;

        // Запрос на получение IP-адреса через API
        fetch('https://api.ipify.org?format=json')
            .then(response => response.json())
            .then(data => {
                document.getElementById('user-ip').value = data.ip;
            });

        // Инициализация капчи при загрузке страницы
        window.onload = generateCaptcha;
    </script>

</body>

</html>
