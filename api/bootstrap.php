<?php
$rootDir = dirname(__DIR__);
$configFile = $rootDir . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Missing config.php. Copy config.example.php and update credentials.']);
    exit;
}
$config = require $configFile;

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

if (!empty($config['session_name'])) {
    session_name($config['session_name']);
}
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $params = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $params['lifetime'],
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

$mysqli = @new mysqli(
    $config['db']['host'] ?? '127.0.0.1',
    $config['db']['user'] ?? 'root',
    $config['db']['password'] ?? '',
    $config['db']['database'] ?? '',
    $config['db']['port'] ?? 3306
);

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => '数据库连接失败: ' . $mysqli->connect_error]);
    exit;
}

if (!empty($config['db']['charset'])) {
    $mysqli->set_charset($config['db']['charset']);
}

function ensure_lessons_description_column(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $result = $mysqli->query("SHOW COLUMNS FROM `lessons` LIKE 'description'");
    if ($result instanceof mysqli_result) {
        $hasColumn = $result->num_rows > 0;
        $result->free();
        if ($hasColumn) {
            return;
        }
    } else {
        return;
    }

    $mysqli->query("ALTER TABLE `lessons` ADD COLUMN `description` TEXT AFTER `video_url`");
}

function ensure_lesson_attachments_column(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $result = $mysqli->query("SHOW COLUMNS FROM `lessons` LIKE 'attachments'");
    if ($result instanceof mysqli_result) {
        $hasColumn = $result->num_rows > 0;
        $result->free();
        if ($hasColumn) {
            return;
        }
    } else {
        return;
    }

    $mysqli->query("ALTER TABLE `lessons` ADD COLUMN `attachments` TEXT NULL DEFAULT NULL AFTER `description`");
}

function ensure_cloud_files_table(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `cloud_files` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `original_name` VARCHAR(255) NOT NULL,
            `stored_name` VARCHAR(255) NOT NULL,
            `mime_type` VARCHAR(150) DEFAULT NULL,
            `size_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `is_public` TINYINT(1) NOT NULL DEFAULT 0,
            `share_token` VARCHAR(64) NOT NULL UNIQUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_cf_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function ensure_course_metadata_columns(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $columnCheck = $mysqli->query("SHOW COLUMNS FROM `courses` LIKE 'instructor'");
    if ($columnCheck instanceof mysqli_result) {
        $hasInstructor = $columnCheck->num_rows > 0;
        $columnCheck->free();
        if (!$hasInstructor) {
            $mysqli->query("ALTER TABLE `courses` ADD COLUMN `instructor` VARCHAR(150) NULL DEFAULT NULL AFTER `description`");
        }
    }

    $tagsCheck = $mysqli->query("SHOW COLUMNS FROM `courses` LIKE 'tags'");
    if ($tagsCheck instanceof mysqli_result) {
        $hasTags = $tagsCheck->num_rows > 0;
        $tagsCheck->free();
        if (!$hasTags) {
            $mysqli->query("ALTER TABLE `courses` ADD COLUMN `tags` VARCHAR(255) NULL DEFAULT NULL AFTER `instructor`");
        }
    }
}

function ensure_course_owner_column(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $columnCheck = $mysqli->query("SHOW COLUMNS FROM `courses` LIKE 'owner_id'");
    if ($columnCheck instanceof mysqli_result) {
        $hasOwner = $columnCheck->num_rows > 0;
        $columnCheck->free();
        if (!$hasOwner) {
            $mysqli->query("ALTER TABLE `courses` ADD COLUMN `owner_id` INT NULL DEFAULT NULL AFTER `tags`");
        }
    }
}

function ensure_teacher_role_enum(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    $mysqli->query("ALTER TABLE `users` MODIFY `role` ENUM('student','admin','teacher') NOT NULL DEFAULT 'student'");
}

function ensure_user_progress_table(mysqli $mysqli): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `user_lesson_progress` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `course_id` INT NOT NULL,
            `lesson_id` INT NOT NULL,
            `visited_at` TIMESTAMP NULL DEFAULT NULL,
            `completed_at` TIMESTAMP NULL DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_user_lesson` (`user_id`, `lesson_id`),
            INDEX `idx_user_course` (`user_id`, `course_id`),
            CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_progress_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_progress_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function error_response(string $message, int $status = 400): void
{
    json_response(['error' => $message], $status);
}

function require_role(mysqli $mysqli, array $roles)
{
    $user = require_login($mysqli);
    if (!in_array($user['role'], $roles, true)) {
        error_response('权限不足', 403);
    }
    return $user;
}

function get_json_input(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'multipart/form-data') !== false) {
        // 文件上传时避免读取整段 php://input 造成内存暴涨
        return [];
    }
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    // 避免过大的请求体占用内存（例如错误的上传请求）
    if (strlen($raw) > 1024 * 1024 * 2) { // 2MB上限
        return [];
    }
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }
    return $data;
}

function current_user(mysqli $mysqli): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $mysqli->prepare('SELECT id, username, display_name, role FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法准备查询用户信息');
    }
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc() ?: null;
    $stmt->close();
    return $user;
}

function require_login(mysqli $mysqli): array
{
    $user = current_user($mysqli);
    if (!$user) {
        error_response('请先登录', 401);
    }
    return $user;
}

function require_admin(mysqli $mysqli): array
{
    return require_role($mysqli, ['admin']);
}

function require_admin_or_teacher(mysqli $mysqli): array
{
    return require_role($mysqli, ['admin', 'teacher']);
}
