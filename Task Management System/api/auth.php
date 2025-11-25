<?php
// Prevent accidental output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// prevent accidental whitespace
ob_start();

header("Content-Type: application/json; charset=UTF-8");

// load DB connection
$dbPath = __DIR__ . "/../db.php";
if (!file_exists($dbPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: missing db.php']);
    exit;
}
require_once $dbPath;

// To check db conn
if (!isset($pdo) || !$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

// Read JSON body if available
$body = null;
$raw = file_get_contents("php://input");
if ($raw) {
    $try = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) $body = $try;
}

// Register Code
if ($action === 'register' && $method === 'POST') {
    $data = $body;
    if (!$data || !isset($data['name'], $data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'name, email and password are required']);
        exit;
    }

    $name = trim($data['name']);
    $email = trim($data['email']);
    $password = $data['password'];

    if ($name === '' || $email === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address']);
        exit;
    }

    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters']);
        exit;
    }

    // check existing email
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        if ($existing) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already registered']);
            exit;
        }

        // insert user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins = $pdo->prepare("INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
        $ins->execute([$name, $email, $hash]);

        // optionally set session
        $newId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $newId;

        echo json_encode(['success' => true, 'id' => $newId]);
        exit;
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        exit;
    }
}

// Login Code
if ($action === 'login' && $method === 'POST') {
    $data = $body;
    if (!$data || !isset($data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'email and password required']);
        exit;
    }

    $email = trim($data['email']);
    $password = $data['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        exit;
    }
}

// Logout Code
if ($action === 'logout') {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// Unknown action
http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
exit;
