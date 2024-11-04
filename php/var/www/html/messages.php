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

    // Запрос для получения всех сообщений
    $sql = "SELECT email, username, message, created_at FROM messages ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);

    // Проверка наличия сообщений
    if ($stmt->rowCount() > 0) {
        // Вывод сообщений в виде списка
        echo "<ul>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<li><strong>Email:</strong> " . htmlspecialchars($row['email']) . "<br>";
            echo "<strong>Имя пользователя:</strong> " . htmlspecialchars($row['username']) . "<br>";
            echo "<strong>Сообщение:</strong> " . nl2br(htmlspecialchars($row['message'])) . "<br>";
            echo "<small><em>Отправлено: " . htmlspecialchars($row['created_at']) . "</em></small></li><br>";
        }
        echo "</ul>";
    } 
    else {
        echo "<p>Сообщений пока нет.</p>";
    }
    ?>
</body>

</html>
