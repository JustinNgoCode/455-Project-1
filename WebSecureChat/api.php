<?php
header("Content-Type: application/json");
session_start();

// Database connection
$host = "localhost";
$dbname = "your_db_name";
$user = "your_db_user";
$pass = "your_db_password";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error"]);
    exit;
}

// Rate limiting (3 requests per minute)
if (!isset($_SESSION['requests'])) $_SESSION['requests'] = [];
$_SESSION['requests'] = array_filter($_SESSION['requests'], fn($t) => $t > time() - 60);
if (count($_SESSION['requests']) >= 3) {
    echo json_encode(["success" => false, "message" => "Too many requests"]);
    exit;
}
$_SESSION['requests'][] = time();

// Handle request
$input = json_decode(file_get_contents("php://input"), true);
$action = $input['action'] ?? '';
$username = filter_var($input['username'] ?? '', FILTER_SANITIZE_STRING);
$password = $input['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(["success" => false, "message" => "Missing username or password"]);
    exit;
}

if ($action === "register") {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(["success" => false, "message" => "Username taken"]);
        exit;
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$username, $hash]);
    echo json_encode(["success" => true]);
} elseif ($action === "login") {
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch();
    if ($row && password_verify($password, $row['password_hash'])) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid credentials"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid action"]);
}
?>