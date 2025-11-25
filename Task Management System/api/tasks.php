<?php
// api/tasks.php
require_once __DIR__ . "/../db.php";
header("Content-Type: application/json; charset=UTF-8");

// Start session if not started
if (session_status() === PHP_SESSION_NONE) session_start();

// require login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

/* -----------------------
   CSV EXPORT (GET ?action=export)
   Returns downloadable CSV of all tasks for the user
-------------------------*/
if ($method === 'GET' && $action === 'export') {
    // fetch all tasks for this user - include category name
    $stmt = $pdo->prepare("SELECT t.title, t.description, t.priority, t.status, COALESCE(c.name,'') AS category, t.due_date
                           FROM tasks t LEFT JOIN categories c ON t.category_id = c.id
                           WHERE t.user_id = ?
                           ORDER BY COALESCE(t.due_date, t.created_at) ASC");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    // send CSV headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=tasks_export.csv');

    $out = fopen('php://output', 'w');
    // header row (as requested)
    fputcsv($out, ['title','description','priority','status','category','due_date']);

    foreach ($rows as $r) {
        fputcsv($out, [$r['title'], $r['description'], $r['priority'], $r['status'], $r['category'], $r['due_date']]);
    }
    fclose($out);
    exit;
}

/* -----------------------
   CSV IMPORT (POST ?action=import)
   Accepts multipart/form-data file upload under 'file'
   Auto-creates categories if missing (Option A)
-------------------------*/
if ($method === 'POST' && $action === 'import') {
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded']);
        exit;
    }

    $file = $_FILES['file']['tmp_name'];
    if (!is_uploaded_file($file)) {
        http_response_code(400);
        echo json_encode(['error' => 'Upload failed']);
        exit;
    }

    // Open CSV
    $handle = fopen($file, 'r');
    if ($handle === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to read uploaded file']);
        exit;
    }

    // Read header row to map columns
    $header = fgetcsv($handle);
    if ($header === false) {
        http_response_code(400);
        echo json_encode(['error' => 'Empty CSV']);
        exit;
    }

    // Normalize header names (lowercase trimmed)
    $cols = array_map(function($c){ return strtolower(trim($c)); }, $header);

    // Expected/allowed columns
    $allowed = ['title','description','priority','status','category','due_date'];

    // Map column index for each allowed column (if present)
    $map = [];
    foreach ($allowed as $col) {
        $idx = array_search($col, $cols);
        $map[$col] = $idx !== false ? $idx : null;
    }

    $inserted = 0;
    $skipped = 0;
    $errors = [];

    // Prepare commonly used statements outside loop
    $selectCat = $pdo->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
    $insertCat = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $insertTask = $pdo->prepare("INSERT INTO tasks (user_id, title, description, priority, status, due_date, category_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

    // Process rows
    while (($row = fgetcsv($handle)) !== false) {
        // read values with mapping
        $title = $map['title'] !== null ? trim($row[$map['title']]) : '';
        if ($title === '') { $skipped++; continue; } // title required

        $description = $map['description'] !== null ? trim($row[$map['description']]) : null;
        $priority = $map['priority'] !== null ? strtolower(trim($row[$map['priority']])) : 'medium';
        if (!in_array($priority, ['low','medium','high'])) $priority = 'medium';

        $status = $map['status'] !== null ? strtolower(trim($row[$map['status']])) : 'pending';
        if (!in_array($status, ['pending','completed'])) $status = 'pending';

        $category_name = $map['category'] !== null ? trim($row[$map['category']]) : '';
        if ($category_name === '') $category_name = 'Others';

        // due_date optional, only accept YYYY-MM-DD or empty
        $due_date = null;
        if ($map['due_date'] !== null) {
            $d = trim($row[$map['due_date']]);
            if ($d !== '') {
                // basic validation (YYYY-MM-DD)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) $due_date = $d;
                else {
                    // attempt to parse common formats
                    $ts = strtotime($d);
                    if ($ts !== false) $due_date = date('Y-m-d', $ts);
                    else $due_date = null;
                }
            }
        }

        // map or create category -> category_id
        $selectCat->execute([$category_name]);
        $c = $selectCat->fetch();
        if ($c) $catId = $c['id'];
        else {
            $insertCat->execute([$category_name]);
            $catId = $pdo->lastInsertId();
        }

        // insert task
        try {
            $insertTask->execute([$userId, $title, $description, $priority, $status, $due_date, $catId]);
            $inserted++;
        } catch (Exception $ex) {
            $errors[] = "Row insertion failed for title '{$title}'";
        }
    }

    fclose($handle);

    echo json_encode(['success'=>true, 'inserted'=>$inserted, 'skipped'=>$skipped, 'errors'=>$errors]);
    exit;
}

/* -----------------------
   Existing REST (GET/POST/PUT/DELETE)
   GET: list or single, supports filters: date, priority, status, search, category
   POST: create (category required), auto-create category if missing
   PUT: update (supports category mapping)
   DELETE: delete task
-------------------------*/

/* GET - list or single */
if ($method === 'GET') {
    if (!empty($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT t.*, c.name AS category_name FROM tasks t LEFT JOIN categories c ON t.category_id = c.id WHERE t.id = ? AND t.user_id = ? LIMIT 1");
        $stmt->execute([$_GET['id'], $userId]);
        echo json_encode($stmt->fetch() ?: []);
        exit;
    }

    $sql = "SELECT t.*, c.name AS category_name FROM tasks t LEFT JOIN categories c ON t.category_id = c.id WHERE t.user_id = ?";
    $params = [$userId];

    if (!empty($_GET['date'])) {
        $sql .= " AND t.due_date = ?";
        $params[] = $_GET['date'];
    }
    if (!empty($_GET['priority'])) {
        $sql .= " AND t.priority = ?";
        $params[] = $_GET['priority'];
    }
    if (!empty($_GET['status'])) {
        $sql .= " AND t.status = ?";
        $params[] = $_GET['status'];
    }
    if (!empty($_GET['search'])) {
        $sql .= " AND (t.title LIKE ? OR t.description LIKE ?)";
        $q = '%' . $_GET['search'] . '%';
        $params[] = $q; $params[] = $q;
    }
    if (isset($_GET['category'])) {
        $cat = $_GET['category'];
        if ($cat !== '') {
            $cstmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
            $cstmt->execute([$cat]);
            $c = $cstmt->fetch();
            if ($c) {
                $sql .= " AND t.category_id = ?";
                $params[] = $c['id'];
            } else {
                echo json_encode([]); exit;
            }
        }
    }

    $sql .= " ORDER BY COALESCE(t.due_date, t.created_at) ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll());
    exit;
}

/* POST - create */
if ($method === 'POST' && $action === null) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['title'])) {
        http_response_code(400);
        echo json_encode(['error'=>'Title required']);
        exit;
    }

    $category_name = isset($data['category']) ? trim($data['category']) : '';
    if ($category_name === '') {
        http_response_code(400);
        echo json_encode(['error'=>'Category required']);
        exit;
    }

    $title = $data['title'];
    $description = $data['description'] ?? null;
    $priority = $data['priority'] ?? 'medium';
    if (!in_array($priority, ['low','medium','high'])) $priority = 'medium';
    $status = $data['status'] ?? 'pending';
    if (!in_array($status, ['pending','completed'])) $status = 'pending';
    $due_date = !empty($data['due_date']) ? $data['due_date'] : null;

    // map or create category
    $cstmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
    $cstmt->execute([$category_name]);
    $c = $cstmt->fetch();
    if ($c) $category_id = $c['id'];
    else {
        $ins = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $ins->execute([$category_name]);
        $category_id = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, priority, status, due_date, category_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $title, $description, $priority, $status, $due_date, $category_id]);

    echo json_encode(['success'=>true, 'id' => $pdo->lastInsertId()]);
    exit;
}

/* PUT - update */
if ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $put);
    $id = $_GET['id'] ?? $put['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

    $allowed = ['title','description','priority','status','due_date','category'];
    $sets = []; $params = [];

    // handle category specially
    if (isset($put['category'])) {
        $catName = trim($put['category']);
        if ($catName === '') { http_response_code(400); echo json_encode(['error'=>'Category required']); exit; }
        $cstmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
        $cstmt->execute([$catName]);
        $c = $cstmt->fetch();
        if ($c) $catId = $c['id'];
        else {
            $ins = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $ins->execute([$catName]);
            $catId = $pdo->lastInsertId();
        }
        $sets[] = "category_id = ?";
        $params[] = $catId;
    }

    foreach ($allowed as $f) {
        if ($f === 'category') continue;
        if (isset($put[$f])) { $sets[] = "$f = ?"; $params[] = $put[$f]; }
    }

    if (empty($sets)) { http_response_code(400); echo json_encode(['error'=>'No fields']); exit; }

    $params[] = $id; $params[] = $userId;
    $sql = "UPDATE tasks SET " . implode(',', $sets) . " WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success'=>true]);
    exit;
}

/* DELETE ALL TASKS for user */
if (isset($_GET["action"]) && $_GET["action"] === "delete_all") {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE user_id = ?");
    $stmt->execute([$userId]);
    echo json_encode(["success" => true]);
    exit;
}


/* DELETE */
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    echo json_encode(['success'=>true]);
    exit;
}



http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
