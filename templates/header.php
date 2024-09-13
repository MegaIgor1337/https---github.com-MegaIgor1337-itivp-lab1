<?php
if (!isset($_SESSION['user_id'])) {
    return; // Если не авторизован, хедер не показываем
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../styles/header.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <h1 class="logo">Real Estate Agency</h1>
            <ul>
                <li><a href="user_dashboard.php">Главная</a></li>
                <?php if ($_SESSION['role_id'] == 2): ?>
                    <li><a href="admin_dashboard.php">Управление</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Выход</a></li>
            </ul>
        </nav>
    </header>
</body>
</html>
