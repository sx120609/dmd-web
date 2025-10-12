<?php
// 数据库修复脚本：根据当前代码所需结构检查并补齐缺失的表与字段。
$rootDir = __DIR__;
$configFile = $rootDir . '/config.php';
$databaseSqlFile = $rootDir . '/database.sql';

if (!file_exists($configFile)) {
    fwrite(STDERR, "未找到 config.php，请先运行 install.php 或手动创建配置文件。\n");
    exit(1);
}

$config = require $configFile;

$mysqli = @new mysqli(
    $config['db']['host'] ?? '127.0.0.1',
    $config['db']['user'] ?? 'root',
    $config['db']['password'] ?? '',
    $config['db']['database'] ?? '',
    $config['db']['port'] ?? 3306
);

if ($mysqli->connect_errno) {
    fwrite(STDERR, "数据库连接失败：{$mysqli->connect_error}\n");
    exit(1);
}

if (!empty($config['db']['charset'])) {
    $mysqli->set_charset($config['db']['charset']);
}

$mysqli->autocommit(true);

$changes = [];
$errors = [];

function runDatabaseSql(mysqli $mysqli, string $sql): void
{
    if ($sql === '') {
        return;
    }

    if (!$mysqli->multi_query($sql)) {
        throw new RuntimeException('执行初始化 SQL 失败：' . $mysqli->error);
    }

    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
        if ($mysqli->errno) {
            throw new RuntimeException('执行初始化 SQL 失败：' . $mysqli->error);
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}

function columnExists(mysqli $mysqli, string $table, string $column): bool
{
    $stmt = $mysqli->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    if (!$stmt) {
        throw new RuntimeException('查询表结构失败：' . $mysqli->error);
    }
    $stmt->bind_param('s', $column);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

function ensureColumn(mysqli $mysqli, string $table, string $column, string $definition, array &$changes): void
{
    if (columnExists($mysqli, $table, $column)) {
        return;
    }

    $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}";
    if (!$mysqli->query($sql)) {
        throw new RuntimeException("添加 {$table}.{$column} 字段失败：" . $mysqli->error);
    }
    $changes[] = "添加字段 {$table}.{$column}";
}

function ensureTable(mysqli $mysqli, string $table): bool
{
    $stmt = $mysqli->prepare("SHOW TABLES LIKE ?");
    if (!$stmt) {
        throw new RuntimeException('查询数据表失败：' . $mysqli->error);
    }
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

try {
    if (!file_exists($databaseSqlFile)) {
        throw new RuntimeException('缺少 database.sql，无法同步表结构。');
    }

    $sql = file_get_contents($databaseSqlFile) ?: '';
    if (strncmp($sql, "\xEF\xBB\xBF", 3) === 0) {
        $sql = substr($sql, 3);
    }
    runDatabaseSql($mysqli, $sql);

    // users 表补充字段
    if (ensureTable($mysqli, 'users')) {
        ensureColumn($mysqli, 'users', 'display_name', 'VARCHAR(150) DEFAULT NULL AFTER `username`', $changes);
        ensureColumn($mysqli, 'users', 'role', "ENUM('student','admin') NOT NULL DEFAULT 'student' AFTER `display_name`", $changes);
        ensureColumn($mysqli, 'users', 'password_hash', 'VARCHAR(255) NOT NULL AFTER `role`', $changes);
        ensureColumn($mysqli, 'users', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `password_hash`', $changes);
    }

    // courses 表补充字段
    if (ensureTable($mysqli, 'courses')) {
        ensureColumn($mysqli, 'courses', 'description', 'TEXT NULL AFTER `title`', $changes);
        ensureColumn($mysqli, 'courses', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `description`', $changes);
    }

    // lessons 表补充字段
    if (ensureTable($mysqli, 'lessons')) {
        ensureColumn($mysqli, 'lessons', 'video_url', 'VARCHAR(500) DEFAULT NULL AFTER `title`', $changes);
        ensureColumn($mysqli, 'lessons', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `video_url`', $changes);
    }

    // live_sessions 表结构
    if (ensureTable($mysqli, 'live_sessions')) {
        ensureColumn($mysqli, 'live_sessions', 'course_id', 'INT NULL AFTER `id`', $changes);
        ensureColumn($mysqli, 'live_sessions', 'title', 'VARCHAR(200) NOT NULL AFTER `course_id`', $changes);
        ensureColumn($mysqli, 'live_sessions', 'description', 'TEXT NULL AFTER `title`', $changes);
        ensureColumn($mysqli, 'live_sessions', 'stream_url', 'VARCHAR(500) NOT NULL AFTER `description`', $changes);
        ensureColumn($mysqli, 'live_sessions', 'starts_at', 'DATETIME DEFAULT NULL AFTER `stream_url`', $changes);
        ensureColumn($mysqli, 'live_sessions', 'ends_at', 'DATETIME DEFAULT NULL AFTER `starts_at`', $changes);
        ensureColumn($mysqli, 'live_sessions', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `ends_at`', $changes);

        // 确保外键约束存在且为 ON DELETE SET NULL
        $fkResult = $mysqli->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'live_sessions' AND COLUMN_NAME = 'course_id' AND REFERENCED_TABLE_NAME = 'courses'");
        $hasForeignKey = $fkResult && $fkResult->num_rows > 0;
        if ($fkResult) {
            $fkResult->free();
        }
        if (!$hasForeignKey) {
            $mysqli->query('ALTER TABLE `live_sessions` ADD CONSTRAINT `fk_live_sessions_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL');
            $changes[] = '为 live_sessions.course_id 补充外键约束';
        }
    }

    // user_courses 与 user_live_sessions 的存在
    if (!ensureTable($mysqli, 'user_courses')) {
        $sql = "CREATE TABLE `user_courses` (
            `user_id` INT NOT NULL,
            `course_id` INT NOT NULL,
            `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`user_id`, `course_id`),
            CONSTRAINT `fk_uc_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_uc_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if (!$mysqli->query($sql)) {
            throw new RuntimeException('创建 user_courses 表失败：' . $mysqli->error);
        }
        $changes[] = '创建 user_courses 表';
    } else {
        ensureColumn($mysqli, 'user_courses', 'assigned_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `course_id`', $changes);
    }

    if (!ensureTable($mysqli, 'user_live_sessions')) {
        $sql = "CREATE TABLE `user_live_sessions` (
            `user_id` INT NOT NULL,
            `live_session_id` INT NOT NULL,
            `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`user_id`, `live_session_id`),
            CONSTRAINT `fk_uls_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_uls_live_session` FOREIGN KEY (`live_session_id`) REFERENCES `live_sessions`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        if (!$mysqli->query($sql)) {
            throw new RuntimeException('创建 user_live_sessions 表失败：' . $mysqli->error);
        }
        $changes[] = '创建 user_live_sessions 表';
    } else {
        ensureColumn($mysqli, 'user_live_sessions', 'assigned_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `live_session_id`', $changes);
    }

    if (empty($changes)) {
        echo "数据库结构已是最新，无需修改。\n";
    } else {
        echo "数据库结构同步完成：\n";
        foreach ($changes as $change) {
            echo " - {$change}\n";
        }
    }

    echo "操作完成。\n";
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}

if ($errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error . "\n");
    }
    $mysqli->close();
    exit(1);
}

$mysqli->close();
exit(0);
