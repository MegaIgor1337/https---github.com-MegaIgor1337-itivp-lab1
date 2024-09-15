<?php
session_start();
require '../includes/dbconnect.php'; 

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit();
}

$limit = 15; 
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;
$error = "";

try {
    $stmt = $conn->prepare("
        SELECT r.id, r.type, r.description, r.rooms, r.degree, r.floor, 
               a.address, a.city, a.postal_code
        FROM real_estate r
        LEFT JOIN real_estate_address a ON r.id = a.real_estate_id
        LIMIT ?, ?
    ");
    $stmt->bind_param("ii", $start, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    $error = "Ошибка при получении данных недвижимости: " . htmlspecialchars($e->getMessage());
}

try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM real_estate");
    $stmt->execute();
    $total_result = $stmt->get_result()->fetch_assoc();
    $total = $total_result['count'];
    $pages = ceil($total / $limit); 
} catch (Exception $e) {
    $error = "Ошибка при подсчете общего количества записей: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Информация о недвижимости</title>
    <link rel="stylesheet" href="../styles/user_dashboard.css">
</head>
<body>
    <header>
        <h1>Информация о недвижимости</h1>
        <a href="logout.php">Выйти</a>
    </header>

    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php else: ?>
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
                </tr>
                <?php endwhile; ?>
            </table>

            <div class="pagination">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>>
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
