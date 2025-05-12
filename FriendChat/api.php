<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

define('ENCRYPTION_KEY', '3jX9kPqW7mZ2rY8tL4nF6vB0cH5xQeR1'); // 32-character key for AES-256
define('RATE_LIMIT', 10); // Max 10 requests per minute
define('RECAPTCHA_SECRET', '6LejTjYrAAAAAEa5YPdCRx-37nxotGZYeThJjzdO'); // Replace with your reCAPTCHA secret key

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            $host = "sql107.infinityfree.com";
            $dbname = "if0_38958162_friendchat";
            $username = "if0_38958162";
            $password = "0VFlChN9Jkn";

            try {
                self::$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                error_log("Database connection established");
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                echo json_encode(["success" => false, "message" => "Database connection failed"]);
                exit;
            }
        }
        return self::$pdo;
    }

    public static function closeConnection() {
        self::$pdo = null;
    }
}

function encryptData($data) {
    return base64_encode(openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, substr(hash('sha256', ENCRYPTION_KEY), 0, 16)));
}

function decryptData($data) {
    return openssl_decrypt(base64_decode($data), 'aes-256-cbc', ENCRYPTION_KEY, 0, substr(hash('sha256', ENCRYPTION_KEY), 0, 16));
}

function sanitizeString($str) {
    return filter_var($str, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH) ?: "";
}

function validateUsername($username) {
    return preg_match("/^[a-zA-Z0-9]{3,}$/", $username) ? $username : false;
}

function validatePassword($password) {
    return strlen($password) >= 6 ? $password : false;
}

function checkRateLimit($action, $ip) {
    $pdo = Database::getConnection();
    $window = 60; // 1 minute window
    $currentTime = date('Y-m-d H:i:s');
    $windowEnd = date('Y-m-d H:i:s', strtotime("+$window seconds"));

    // Clean up expired requests
    $stmt = $pdo->prepare("DELETE FROM requests WHERE window_end < ?");
    $stmt->execute([$currentTime]);

    // Check existing rate limit
    $stmt = $pdo->prepare("SELECT request_count FROM requests WHERE ip_address = ? AND action = ? AND window_end > ?");
    $stmt->execute([$ip, $action, $currentTime]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['request_count'] >= RATE_LIMIT) {
        error_log("Rate limit exceeded for IP: $ip, action: $action");
        return false;
    }

    // Update or insert request count
    if ($row) {
        $stmt = $pdo->prepare("UPDATE requests SET request_count = request_count + 1, last_request = ?, window_end = ? WHERE ip_address = ? AND action = ?");
        $stmt->execute([$currentTime, $windowEnd, $ip, $action]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO requests (ip_address, action, request_count, last_request, window_end) VALUES (?, ?, 1, ?, ?)");
        $stmt->execute([$ip, $action, $currentTime, $windowEnd]);
    }
    return true;
}

function verifyRecaptcha($recaptchaResponse) {
    if (empty($recaptchaResponse)) return false;
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);
    return $response['success'] ?? false;
}

$pdo = Database::getConnection();
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? strtolower($_SERVER["CONTENT_TYPE"]) : "";
$input = strpos($contentType, "application/json") !== false ? (json_decode(file_get_contents("php://input"), true) ?: []) : [];
$action = $input["action"] ?? $_POST["action"] ?? "";

error_log("Received action: $action from IP: $ip");

if (!checkRateLimit($action, $ip)) {
    echo json_encode(["success" => false, "message" => "Rate limit exceeded. Try again later."]);
    Database::closeConnection();
    exit;
}

// Special handling for login/register with reCAPTCHA
if (in_array($action, ['register', 'login'])) {
    $recaptchaResponse = $input['recaptcha'] ?? '';
    if (!verifyRecaptcha($recaptchaResponse)) {
        error_log("reCAPTCHA verification failed for IP: $ip, action: $action");
        echo json_encode(["success" => false, "message" => "reCAPTCHA verification failed"]);
        Database::closeConnection();
        exit;
    }
}

if ($action === "register") {
    $username = sanitizeString($input["username"] ?? "");
    $password = $input["password"] ?? "";
    $recaptcha = $input["recaptcha"] ?? "";

    if (!validateUsername($username) || !validatePassword($password)) {
        error_log("Validation failed: Invalid username or password");
        echo json_encode(["success" => false, "message" => "Username (min 3 alphanumeric) and password (min 6) required"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            error_log("Username already exists: $username");
            echo json_encode(["success" => false, "message" => "Username already exists"]);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        error_log("User registered: $username");
        echo json_encode(["success" => true, "message" => "Registration successful"]);
    } catch (Exception $e) {
        error_log("Registration failed: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Registration failed"]);
        exit;
    }
} elseif ($action === "login") {
    $username = sanitizeString($input["username"] ?? "");
    $password = $input["password"] ?? "";
    $recaptcha = $input["recaptcha"] ?? "";

    if (!validateUsername($username) || !$password) {
        error_log("Validation failed: Invalid credentials format");
        echo json_encode(["success" => false, "message" => "Invalid credentials format"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            error_log("Login successful: $username");
            echo json_encode(["success" => true]);
        } else {
            error_log("Login failed: Invalid credentials for $username");
            echo json_encode(["success" => false, "message" => "Invalid credentials"]);
        }
    } catch (Exception $e) {
        error_log("Login failed: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Login failed"]);
        exit;
    }
} elseif ($action === "sendMessage") {
    $sender = sanitizeString($input["sender"] ?? "");
    $content = $input["content"] ?? "";

    if (!validateUsername($sender) || !$content) {
        error_log("Validation failed: Invalid sender or empty content");
        echo json_encode(["success" => false, "message" => "Valid sender and content required"]);
        exit;
    }

    $encryptedContent = encryptData($content);
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (sender, content, type, timestamp) VALUES (?, ?, 'text', NOW())");
        $stmt->execute([$sender, $encryptedContent]);
        error_log("Message sent by: $sender");
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        error_log("Send message failed: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Send message failed"]);
        exit;
    }
} elseif ($action === "uploadFile") {
    $sender = sanitizeString($_POST["sender"] ?? "");
    if (!validateUsername($sender) || !isset($_FILES["file"])) {
        error_log("File upload failed: Invalid sender or file missing");
        echo json_encode(["success" => false, "message" => "Valid sender and content required"]);
        exit;
    }

    $file = $_FILES["file"];
    $uploadDir = "Uploads/";
    $fileName = time() . "_" . basename($file["name"]);
    $filePath = $uploadDir . $fileName;

    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create uploads directory");
            echo json_encode(["success" => false, "message" => "Failed to create uploads directory"]);
            exit;
        }
    }

    if ($file["size"] > 10 * 1024 * 1024) {
        error_log("File upload failed: File too large");
        echo json_encode(["success" => false, "message" => "File too large (max 10MB)"]);
        exit;
    }

    $allowedTypes = ["image/jpeg", "image/png", "image/gif", "application/pdf"];
    if (!in_array($file["type"], $allowedTypes)) {
        error_log("File upload failed: Invalid file type");
        echo json_encode(["success" => false, "message" => "Invalid file type (allowed: JPEG, PNG, GIF, PDF)"]);
        exit;
    }

    try {
        if (move_uploaded_file($file["tmp_name"], $filePath)) {
            $stmt = $pdo->prepare("INSERT INTO messages (sender, content, type, file_path, timestamp) VALUES (?, ?, 'file', ?, NOW())");
            $stmt->execute([$sender, $fileName, $filePath]);
            error_log("File uploaded by: $sender, path: $filePath");
            echo json_encode(["success" => true]);
        } else {
            error_log("File upload failed: Could not move file");
            echo json_encode(["success" => false, "message" => "Failed to upload file"]);
        }
    } catch (Exception $e) {
        error_log("File upload failed: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "File upload failed"]);
        exit;
    }
} elseif ($action === "getMessages") {
    try {
        $stmt = $pdo->query("SELECT sender, content, type, file_path, timestamp FROM messages ORDER BY id ASC");
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($messages as &$msg) {
            if ($msg["type"] === "text") {
                $msg["content"] = decryptData($msg["content"]);
            }
        }
        error_log("Messages retrieved: " . count($messages));
        echo json_encode(["success" => true, "messages" => $messages]);
    } catch (Exception $e) {
        error_log("Get messages failed: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Get messages failed"]);
        exit;
    }
} elseif ($action === "logEvent") {
    $event = sanitizeString($input["event"] ?? "Unknown event");
    try {
        $stmt = $pdo->prepare("INSERT INTO logs (event, timestamp) VALUES (?, NOW())");
        $stmt->execute([$event]);
        error_log("Log event recorded: $event");
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        error_log("Log event failed: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Log event failed"]);
        exit;
    }
} else {
    error_log("Invalid action received: $action");
    echo json_encode(["success" => false, "message" => "Invalid action"]);
}

Database::closeConnection();
?>