<?php
session_start();
require '../includes/dbconnect.php'; // Подключаем файл для работы с базой данных

// Проверка, что пользователь — админ
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    header("Location: login.php");
    exit();
}

// Инициализация переменной для ошибок
$error = "";

// Проверка, что указан ID для редактирования
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$real_estate_id = $_GET['id'];

// Получение текущих данных недвижимости
$stmt = $conn->prepare("
    SELECT r.type, r.description, r.rooms, r.degree, r.floor, 
           a.address, a.city, a.postal_code
    FROM real_estate r
    LEFT JOIN real_estate_address a ON r.id = a.real_estate_id
    WHERE r.id = ?
");
$stmt->bind_param("i", $real_estate_id);
$stmt->execute();
$current_data = $stmt->get_result()->fetch_assoc();

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_real_estate'])) {
    $type = $_POST['type'];
    $description = $_POST['description'];
    $rooms = $_POST['rooms'];
    $degree = $_POST['degree'];
    $floor = $_POST['floor'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];

    $conn->begin_transaction(); // Начинаем транзакцию

    try {
        // Обновляем данные недвижимости
        $stmt = $conn->prepare("
            UPDATE real_estate 
            SET type = ?, description = ?, rooms = ?, degree = ?, floor = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ssissi", $type, $description, $rooms, $degree, $floor, $real_estate_id);
        $stmt->execute();

        // Обновляем адрес недвижимости
        $stmt = $conn->prepare("
            UPDATE real_estate_address 
            SET address = ?, city = ?, postal_code = ? 
            WHERE real_estate_id = ?
        ");
        $stmt->bind_param("sssi", $address, $city, $postal_code, $real_estate_id);
        $stmt->execute();

        $conn->commit(); // Подтверждаем транзакцию

        // Перенаправление после успешного обновления
        header("Location: admin_dashboard.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback(); // Откатываем изменения при ошибке
        $error = "Ошибка при обновлении недвижимости: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование недвижимости</title>
    <link rel="stylesheet" href="../styles/admin_edit.css">
</head>
<body>
    <header>
        <h1>Редактирование недвижимости</h1>
        <a href="logout.php">Выйти</a>
    </header>

    <div class="container">
        <form method="post" action="">
            <input type="text" name="type" placeholder="Тип недвижимости (Дом/Апартаменты)" value="<?php echo htmlspecialchars($current_data['type']); ?>" required>
            <textarea name="description" placeholder="Описание" required><?php echo htmlspecialchars($current_data['description']); ?></textarea>
            <input type="number" name="rooms" placeholder="Количество комнат" value="<?php echo htmlspecialchars($current_data['rooms']); ?>" required>
            <input type="text" name="degree" placeholder="Состояние (новая/требует ремонта)" value="<?php echo htmlspecialchars($current_data['degree']); ?>" required>
            <input type="number" name="floor" placeholder="Этаж" value="<?php echo htmlspecialchars($current_data['floor']); ?>" required>
            <input type="text" name="address" placeholder="Адрес" value="<?php echo htmlspecialchars($current_data['address']); ?>" required>
            <input type="text" name="city" placeholder="Город" value="<?php echo htmlspecialchars($current_data['city']); ?>" required>
            <input type="text" name="postal_code" placeholder="Почтовый индекс" value="<?php echo htmlspecialchars($current_data['postal_code']); ?>" required>
            <button type="submit" name="update_real_estate">Обновить недвижимость</button>
        </form>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
