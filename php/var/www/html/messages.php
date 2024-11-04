<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр сообщений</title>
    <link rel="stylesheet" href="css/messages.css">
</head>

<body>
    <h2>Все сообщения</h2>

    <?php
    include 'db.php'; // Подключение к базе данных

    // Обработка запроса на удаление сообщения
    if (isset($_POST['delete'])) {
        $id = $_POST['id']; // Получаем ID сообщения для удаления
        $deleteSql = "DELETE FROM messages WHERE id = :id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->bindValue(':id', $id, PDO::PARAM_INT);
        $deleteStmt->execute();
    }

    // Параметры постраничного вывода
    $messagesPerPage = 5;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $messagesPerPage;

    // Запрос для получения сообщений с ограничением на количество и смещением
    $sql = "SELECT id, email, username, message, created_at, user_ip, user_browser FROM messages ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $messagesPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    // Подсчет общего количества сообщений для расчета страниц
    $totalMessages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    $totalPages = ceil($totalMessages / $messagesPerPage);

    // Проверка наличия сообщений
    if ($stmt->rowCount() > 0) {
        echo "<table id='messagesTable'>";
        echo "<thead><tr>";
        echo "<th onclick='sortTable(0)'>Email</th>";
        echo "<th onclick='sortTable(1)'>Имя пользователя</th>";
        echo "<th>Сообщение</th>";
        echo "<th>IP Адрес</th>";
        echo "<th>Информация о браузере</th>";
        echo "<th onclick='sortTable(5)'>Отправлено</th>";
        echo "<th>Действия</th>"; // Новый столбец для действий
        echo "</tr></thead>";
        echo "<tbody>";
        
        // Вывод сообщений в таблицу
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($row['message'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['user_ip']) . "</td>";
            echo "<td>" . htmlspecialchars($row['user_browser']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "<td><form method='post' style='display:inline;'>
                    <input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>
                    <button type='submit' name='delete' onclick='return confirm(\"Вы уверены, что хотите удалить это сообщение?\");'>Удалить</button>
                </form></td>"; // Кнопка удаления
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        
        // Постраничная навигация
        echo "<div class='pagination'>";
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $page) {
                echo "<span class='current-page'>$i</span>";
            } else {
                echo "<a href='?page=$i'>$i</a>";
            }
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

                if (columnIndex === 5) { // Сортировка по дате
                    return isAscending ? new Date(cellA) - new Date(cellB) : new Date(cellB) - new Date(cellA);
                } else { // Сортировка по тексту
                    return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
                }
            });

            const tbody = table.querySelector("tbody");
            rows.forEach(row => tbody.appendChild(row));
        }
    </script>
</body>

</html>
