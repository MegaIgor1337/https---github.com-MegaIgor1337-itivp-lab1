<?php
session_start();
require '../includes/dbconnect.php'; 

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
} else {
    $error = "";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    function loginUser($username, $password, $conn) {
        global $error;

        try {
            $sql = "SELECT * FROM user_info WHERE username = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role_id'] = $user['role_id'];

                        if ($_SESSION['role_id'] == 1) {
                            header("Location: user_dashboard.php");
                        } elseif ($_SESSION['role_id'] == 2) {
                            header("Location: admin_dashboard.php");
                        }
                        exit;
                    } else {
                        $_SESSION['error'] = "Неверный пароль.";
                    }
                } else {
                    $_SESSION['error'] = "Пользователь не найден.";
                }
            } else {
                $_SESSION['error'] = "Ошибка при подготовке запроса.";
            }
        } catch (mysqli_sql_exception $e) {
            $_SESSION['error'] = "Ошибка выполнения запроса: " . $e->getMessage();
        }

        header("Location: login.php");
        exit();
    }

    loginUser($username, $password, $conn);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>
    <div class="login-container">
        <h1>Вход</h1>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Имя пользователя" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        </form>
        <div class="register-link">
            <p>Нет аккаунта? <a href="registration.php">Зарегистрируйтесь</a></p>
        </div>
    </div>
</body>
</html>
