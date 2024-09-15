<?php
session_start();
require '../includes/dbconnect.php'; 

// Проверка роли пользователя (только для администратора)
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    header("Location: login.php");
    exit();
}

$error = "";

// Обработка добавления новой недвижимости
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_real_estate'])) {
    $type = $_POST['type'];
    $description = $_POST['description'];
    $rooms = $_POST['rooms'];
    $degree = $_POST['degree'];
    $floor = $_POST['floor'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];

    $conn->begin_transaction(); 

    try {
        $stmt = $conn->prepare("INSERT INTO real_estate (type, description, rooms, degree, floor) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisi", $type, $description, $rooms, $degree, $floor);
        $stmt->execute();
        $real_estate_id = $stmt->insert_id;

        $stmt = $conn->prepare("INSERT INTO real_estate_address (real_estate_id, address, city, postal_code) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $real_estate_id, $address, $city, $postal_code);
        $stmt->execute();

        $conn->commit(); 

        header("Location: admin_dashboard.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback(); 
        $error = "Ошибка при добавлении недвижимости: " . $e->getMessage();
    }
}

// Удаление недвижимости
if (isset($_GET['delete'])) {
    $real_estate_id = $_GET['delete'];

    $conn->begin_transaction(); 

    try {
        $stmt = $conn->prepare("DELETE FROM real_estate_address WHERE real_estate_id = ?");
        $stmt->bind_param("i", $real_estate_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM real_estate WHERE id = ?");
        $stmt->bind_param("i", $real_estate_id);
        $stmt->execute();

        $conn->commit(); 

        header("Location: admin_dashboard.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback(); 
        $error = "Ошибка при удалении недвижимости: " . $e->getMessage();
    }
}

// Обработка поиска
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $search_param = "%" . $conn->real_escape_string($search) . "%";

    // Поиск по всем полям
    $stmt = $conn->prepare("
        SELECT r.id, r.type, r.description, r.rooms, r.degree, r.floor, 
               a.address, a.city, a.postal_code
        FROM real_estate r
        LEFT JOIN real_estate_address a ON r.id = a.real_estate_id
        WHERE r.type LIKE ? 
        OR r.description LIKE ? 
        OR r.rooms LIKE ? 
        OR r.degree LIKE ? 
        OR r.floor LIKE ? 
        OR a.address LIKE ? 
        OR a.city LIKE ? 
        OR a.postal_code LIKE ?
    ");
    $stmt->bind_param("ssssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Получение всех записей недвижимости по умолчанию
    $result = $conn->query("
        SELECT r.id, r.type, r.description, r.rooms, r.degree, r.floor, 
               a.address, a.city, a.postal_code
        FROM real_estate r
        LEFT JOIN real_estate_address a ON r.id = a.real_estate_id
    ");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель Администратора</title>
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
</head>
<body>
    <header>
        <h1>Панель информации</h1>
        <a href="logout.php">Выйти</a>
    </header>

    <div class="container">
        <h2>Добавить новую недвижимость</h2>
        <form method="post" action="">
            <input type="text" name="type" placeholder="Тип недвижимости (Дом/Апартаменты)" required>
            <textarea name="description" placeholder="Описание" required></textarea>
            <input type="number" name="rooms" placeholder="Количество комнат" required>
            <input type="text" name="degree" placeholder="Состояние (новая/требует ремонта)" required>
            <input type="number" name="floor" placeholder="Этаж" required>
            <input type="text" name="address" placeholder="Адрес" required>
            <input type="text" name="city" placeholder="Город" required>
            <input type="text" name="postal_code" placeholder="Почтовый индекс" required>
            <button type="submit" name="add_real_estate">Добавить недвижимость</button>
        </form>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h2>Поиск недвижимости</h2>
        <form method="get" action="">
            <input type="text" name="search" placeholder="Введите ключевое слово" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Поиск</button>
        </form>

        <h2>Список недвижимости</h2>
        <table>
            <tr>
                <th>Тип</th>
                <th>Описание</th>
                <th>Комнат</th>
                <th>Состояние</th>
                <th>Этаж</th>
                <th>Адрес</th>
                <th>Город</th>
                <th>Почтовый индекс</th>
                <th>Действия</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo htmlspecialchars($row['rooms']); ?></td>
                <td><?php echo htmlspecialchars($row['degree']); ?></td>
                <td><?php echo htmlspecialchars($row['floor']); ?></td>
                <td><?php echo htmlspecialchars($row['address']); ?></td>
                <td><?php echo htmlspecialchars($row['city']); ?></td>
                <td><?php echo htmlspecialchars($row['postal_code']); ?></td>
                <td>
                    <a href="admin_edit.php?id=<?php echo $row['id']; ?>">Изменить</a> |
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
