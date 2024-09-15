<?php
session_start();
require '../includes/dbconnect.php'; 

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Пароли не совпадают.";
    } else {
        $sql = "SELECT * FROM user_info WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Имя пользователя уже занято.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $role_id = 2; 

                $sql = "INSERT INTO user_info (username, password, role_id) VALUES (?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssi", $username, $hashed_password, $role_id);
                    if ($stmt->execute()) {
                        $new_user_id = $stmt->insert_id;

                        $_SESSION['user_id'] = $new_user_id;
                        $_SESSION['role_id'] = $role_id;

                        header("Location: admin_dashboard.php");
                        exit();
                    } else {
                        $error = "Ошибка при регистрации.";
                    }
                }
            }
        } else {
            $error = "Ошибка при подключении к базе данных.";
        }
    }

    if (!empty($error)) {
        $_SESSION['error'] = $error;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="../styles/registration.css">
</head>
<body>
    <div class="register-container">
        <h1>Регистрация</h1>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Имя пользователя" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <input type="password" name="confirm_password" placeholder="Подтвердите пароль" required>
            <button type="submit">Зарегистрироваться</button>
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        </form>
        <div class="login-link">
            <p>Уже есть аккаунт? <a href="login.php">Войдите</a></p>
        </div>
    </div>
</body>
</html>
