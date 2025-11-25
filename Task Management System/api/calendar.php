<?php
// FIX: prevent any accidental output (removes the <br><b> error)
ob_clean();

// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../db.php";

header("Content-Type: application/json");

// Check login
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Validate
$year = $_GET["year"] ?? null;
$month = $_GET["month"] ?? null;

if (!$year || !$month) {
    http_response_code(400);
    echo json_encode(["error" => "Missing year or month"]);
    exit;
}

// Fetch counts of ONLY pending tasks
$stmt = $pdo->prepare("
    SELECT due_date, COUNT(*) AS count
    FROM tasks
    WHERE user_id = ?
      AND status = 'pending'
      AND YEAR(due_date) = ?
      AND MONTH(due_date) = ?
    GROUP BY due_date
");

$stmt->execute([
    $_SESSION["user_id"],
    $year,
    $month
]);

echo json_encode($stmt->fetchAll());
exit;
