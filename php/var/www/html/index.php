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
    include 'db.php'; // Подключаемся к базе данных

    if (isset($_POST['submit'])) {
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

    // Пагинация
    $limit = 5; // Количество сообщений на странице
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Запрос для получения сообщений с пагинацией
    $sql = "SELECT email, username, message, created_at, user_ip, user_browser FROM messages ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    // Проверка наличия сообщений
    if ($stmt->rowCount() > 0) {
        // Вывод сообщений в виде таблицы
        echo "<h2>Все сообщения</h2>";
        echo "<table id='messagesTable'>";
        echo "<thead><tr><th onclick='sortTable(1)'>Имя пользователя</th><th onclick='sortTable(0)'>Email</th><th onclick='sortTable(3)'>Сообщение</th><th>Отправлено</th></tr></thead>";
        echo "<tbody>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($row['message'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";

        // Получение общего количества сообщений для пагинации
        $countSql = "SELECT COUNT(*) FROM messages";
        $countStmt = $pdo->query($countSql);
        $totalMessages = $countStmt->fetchColumn();
        $totalPages = ceil($totalMessages / $limit);

        // Вывод навигации по страницам
        echo "<div class='pagination'>";
        for ($i = 1; $i <= $totalPages; $i++) {
            echo "<a href='?page=$i'>$i</a> ";
        }
        echo "</div>";
    } else {
        echo "<p>Сообщений пока нет.</p>";
    }
    ?>

    <script>
        // Функция для сортировки таблицы
        let sortOrder = {};
        
        function sortTable(columnIndex) {
            const table = document.getElementById('messagesTable');
            const rows = Array.from(table.querySelectorAll("tbody tr"));

            const isAscending = sortOrder[columnIndex] === 'asc' ? false : true;
            sortOrder[columnIndex] = isAscending ? 'asc' : 'desc';

            rows.sort((a, b) => {
                const cellA = a.cells[columnIndex].textContent.trim();
                const cellB = b.cells[columnIndex].textContent.trim();

                if (columnIndex === 3) { // Сортировка по дате
                    return isAscending ? new Date(cellA) - new Date(cellB) : new Date(cellB) - new Date(cellA);
                } else { // Сортировка по тексту
                    return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
                }
            });

            const tbody = table.querySelector("tbody");
            rows.forEach(row => tbody.appendChild(row));
        }

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
