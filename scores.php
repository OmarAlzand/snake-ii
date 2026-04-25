<?php
/**
 * Snake II — Leaderboard API
 *
 * Endpoints:
 *   GET  scores.php?action=get       Returns top 5 scores
 *   POST scores.php  body=add+score  Submits a new score (requires token)
 *
 * Database table is created automatically on first request.
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);


// ── Connect to database ──────────────────────────────────────────
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}


// ── Create table on first run ────────────────────────────────────
$pdo->exec('
    CREATE TABLE IF NOT EXISTS scores (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        name       VARCHAR(10) NOT NULL,
        score      INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
');


// ── Helper: fetch top 5 scores ───────────────────────────────────
function getTopScores($pdo) {
    $stmt = $pdo->query('
        SELECT name AS n, score AS s
        FROM scores
        ORDER BY score DESC, created_at ASC
        LIMIT 5
    ');
    $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($scores as &$row) {
        $row['s'] = (int) $row['s'];
    }
    return $scores;
}


// ── Parse request ────────────────────────────────────────────────
$action = $_GET['action'] ?? '';
$input = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';
}


// ── GET: return current leaderboard ──────────────────────────────
if ($action === 'get') {
    echo json_encode(['scores' => getTopScores($pdo)]);
    exit;
}


// ── POST add: submit a new score ─────────────────────────────────
if ($action === 'add') {
    $name  = strtoupper(substr(trim($input['name']  ?? 'PLAYER'), 0, 10));
    $score = (int) ($input['score'] ?? 0);
    $moves = (int) ($input['moves'] ?? 0);
    $time  = (int) ($input['time']  ?? 0);
    $token = $input['token'] ?? '';

    $valid  = true;
    $reason = '';

    // 1. Verify the score token (proves the score came from a real game).
    //    The client computed: sha256(score|moves|time|APP_SECRET)
    $expectedToken = hash('sha256', $score . '|' . $moves . '|' . $time . '|' . APP_SECRET);
    if (!hash_equals($expectedToken, $token)) {
        $valid = false;
        $reason = 'Invalid token';
    }

    // 2. Sanity checks: minimum time and moves per food eaten
    if ($valid && $score > 0) {
        if ($time  < $score * 400) { $valid = false; $reason = 'Too fast'; }
        if ($moves < $score * 3)   { $valid = false; $reason = 'Too few moves'; }
    }

    // 3. Bounds checks
    if ($score <= 0 || $score > 999 || strlen($name) === 0) {
        $valid = false;
        $reason = 'Invalid data';
    }

    if ($valid) {
        $stmt = $pdo->prepare('INSERT INTO scores (name, score) VALUES (?, ?)');
        $stmt->execute([$name, $score]);

        // Trim table to top 50 (saves database space)
        $pdo->exec('
            DELETE FROM scores
            WHERE id NOT IN (
                SELECT id FROM (
                    SELECT id FROM scores ORDER BY score DESC LIMIT 50
                ) AS top_scores
            )
        ');
    }

    $response = ['scores' => getTopScores($pdo)];
    if (!$valid) $response['rejected'] = $reason;
    echo json_encode($response);
    exit;
}


// ── Unknown action ───────────────────────────────────────────────
echo json_encode([
    'error' => 'Unknown action. Use ?action=get or POST { action: "add", ... }'
]);
