<?php

$servername = "localhost";
$username = "root";
$password = "";
$database_name = "building_agent";

try {
    $conn = new mysqli($servername, $username, $password, $database_name);

    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных: " . $conn->connect_error);
    }
} catch (Exception $e) {
    echo "Произошла ошибка при подключении к базе данных: " . htmlspecialchars($e->getMessage());
    exit();
}
?>
