<?php
// 简易一次性安装脚本：填写数据库及管理员信息，生成 config.php 并初始化数据表。
$rootDir = __DIR__;
$configFile = $rootDir . '/config.php';
$databaseSqlFile = $rootDir . '/database.sql';

$configExists = file_exists($configFile) && filesize($configFile) > 0;
$errors = [];
$success = false;

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$configExists) {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbPort = (int)($_POST['db_port'] ?? 3306);
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';
    $dbName = trim($_POST['db_name'] ?? '');
    $sessionName = trim($_POST['session_name'] ?? 'COURSESESSID');

    $adminUsername = trim($_POST['admin_username'] ?? '');
    $adminDisplayName = trim($_POST['admin_display_name'] ?? '');
    $adminPassword = $_POST['admin_password'] ?? '';

    if ($dbHost === '' || $dbUser === '' || $dbName === '') {
        $errors[] = '请填写完整的数据库连接信息（主机、用户名、数据库名）。';
    }
    if ($adminUsername === '' || $adminPassword === '') {
        $errors[] = '请填写管理员用户名和密码。';
    }

    if (!file_exists($databaseSqlFile)) {
        $errors[] = '缺少 database.sql 文件，无法初始化数据库结构。';
    }

    if (!$errors) {
        $mysqli = @new mysqli($dbHost, $dbUser, $dbPass, '', $dbPort);
        if ($mysqli->connect_errno) {
            $errors[] = '无法连接数据库服务器：' . $mysqli->connect_error;
        } else {
            $mysqli->set_charset('utf8mb4');
            $dbNameEscaped = '`' . $mysqli->real_escape_string($dbName) . '`';
            if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS {$dbNameEscaped} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                $errors[] = '创建数据库失败：' . $mysqli->error;
            } elseif (!$mysqli->select_db($dbName)) {
                $errors[] = '选择数据库失败：' . $mysqli->error;
            } else {
                $sql = file_get_contents($databaseSqlFile);
                if ($sql === false) {
                    $errors[] = '读取 database.sql 文件失败。';
                } else {
                    if (strncmp($sql, "\xEF\xBB\xBF", 3) === 0) {
                        $sql = substr($sql, 3);
                    }
                    if (!$mysqli->multi_query($sql)) {
                        $errors[] = '执行数据库初始化语句失败：' . $mysqli->error;
                    } else {
                        do {
                            if ($result = $mysqli->store_result()) {
                                $result->free();
                            }
                            if ($mysqli->errno) {
                                $errors[] = '执行数据库初始化语句失败：' . $mysqli->error;
                                break;
                            }
                        } while ($mysqli->more_results() && $mysqli->next_result());

                        if (!$errors) {
                            $usersTableReady = false;
                            if ($check = $mysqli->query("SHOW COLUMNS FROM `users` LIKE 'username'")) {
                                $usersTableReady = $check->num_rows > 0;
                                $check->free();
                            } else {
                                $errors[] = '检测 users 表结构时失败：' . $mysqli->error;
                            }

                            if (!$usersTableReady && !$errors) {
                                $errors[] = '数据库初始化未成功：未找到 users 表或缺少 username 字段，请确认 database.sql 已正确执行。';
                            }
                        }

                        if (!$errors) {
                            $alterStatements = [
                                "ALTER TABLE `live_sessions` MODIFY `course_id` INT NULL",
                                "ALTER TABLE `live_sessions` DROP FOREIGN KEY `fk_live_sessions_course`",
                                "ALTER TABLE `live_sessions` ADD CONSTRAINT `fk_live_sessions_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL",
                            ];
                            foreach ($alterStatements as $statement) {
                                $mysqli->query($statement);
                            }
                        }

                        if (!$errors) {
                            $stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
                            if (!$stmt) {
                                $errors[] = '准备检查管理员用户时失败：' . $mysqli->error;
                            } else {
                                $stmt->bind_param('s', $adminUsername);
                                $stmt->execute();
                                $stmt->store_result();
                                $exists = $stmt->num_rows > 0;
                                $stmt->close();

                                if ($exists) {
                                    $errors[] = '管理员用户名已存在，请选择其他用户名。';
                                } else {
                                    $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
                                    $displayName = $adminDisplayName !== '' ? $adminDisplayName : $adminUsername;
                                    $stmt = $mysqli->prepare('INSERT INTO users (username, display_name, role, password_hash) VALUES (?, ?, "admin", ?)');
                                    if (!$stmt) {
                                        $errors[] = '创建管理员用户失败：' . $mysqli->error;
                                    } else {
                                        $stmt->bind_param('sss', $adminUsername, $displayName, $passwordHash);
                                        if (!$stmt->execute()) {
                                            $errors[] = '插入管理员用户失败：' . $stmt->error;
                                        }
                                        $stmt->close();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$errors) {
            $config = [
                'db' => [
                    'host' => $dbHost,
                    'user' => $dbUser,
                    'password' => $dbPass,
                    'database' => $dbName,
                    'port' => $dbPort,
                    'charset' => 'utf8mb4',
                ],
                'session_name' => $sessionName !== '' ? $sessionName : 'COURSESESSID',
            ];

            $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
            if (file_put_contents($configFile, $configContent) === false) {
                $errors[] = '写入 config.php 失败，请确认目录可写。';
            } else {
                $success = true;
                $configExists = true;
            }
        }

        if (isset($mysqli) && $mysqli instanceof mysqli) {
            $mysqli->close();
        }
    }
}

?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>安装 - 简易录播课系统</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6f8; padding: 40px; }
        .container { max-width: 720px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 4px 18px rgba(0,0,0,0.08); }
        h1 { margin-top: 0; }
        .field { margin-bottom: 16px; }
        label { display: block; font-weight: bold; margin-bottom: 6px; }
        input { width: 100%; padding: 10px; border: 1px solid #ccd0d5; border-radius: 4px; }
        .row { display: flex; gap: 12px; }
        .row .field { flex: 1; }
        .errors { background: #ffe3e3; color: #a30000; border: 1px solid #ffb3b3; padding: 12px; border-radius: 4px; margin-bottom: 16px; }
        .success { background: #e6ffed; color: #0a7a3f; border: 1px solid #a3f7ba; padding: 16px; border-radius: 4px; margin-bottom: 16px; }
        button { background: #1e88e5; color: #fff; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:disabled { background: #cfd8dc; cursor: not-allowed; }
        .notice { margin-top: 24px; font-size: 14px; color: #555; }
    </style>
</head>
<body>
<div class="container">
    <h1>简易录播课系统安装</h1>
    <?php if ($configExists): ?>
        <?php if ($success): ?>
            <div class="success">
                安装完成！请立即删除 install.php 文件，并使用刚才创建的管理员账号登录系统。
            </div>
        <?php else: ?>
            <p>系统检测到 <code>config.php</code> 已存在，如需重新安装，请删除或重命名该文件后刷新本页。</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="errors">
            <p><strong>安装过程中出现以下问题：</strong></p>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$configExists): ?>
    <form method="post">
        <h2>数据库设置</h2>
        <div class="row">
            <div class="field">
                <label for="db_host">数据库主机</label>
                <input type="text" id="db_host" name="db_host" value="<?php echo h($_POST['db_host'] ?? '127.0.0.1'); ?>" required>
            </div>
            <div class="field">
                <label for="db_port">端口</label>
                <input type="number" id="db_port" name="db_port" value="<?php echo h($_POST['db_port'] ?? '3306'); ?>">
            </div>
        </div>
        <div class="row">
            <div class="field">
                <label for="db_user">数据库用户名</label>
                <input type="text" id="db_user" name="db_user" value="<?php echo h($_POST['db_user'] ?? ''); ?>" required>
            </div>
            <div class="field">
                <label for="db_pass">数据库密码</label>
                <input type="password" id="db_pass" name="db_pass" value="<?php echo h($_POST['db_pass'] ?? ''); ?>">
            </div>
        </div>
        <div class="field">
            <label for="db_name">数据库名称</label>
            <input type="text" id="db_name" name="db_name" value="<?php echo h($_POST['db_name'] ?? 'course_platform'); ?>" required>
        </div>
        <div class="field">
            <label for="session_name">Session 名称（可选）</label>
            <input type="text" id="session_name" name="session_name" value="<?php echo h($_POST['session_name'] ?? 'COURSESESSID'); ?>">
        </div>

        <h2>管理员账号</h2>
        <div class="field">
            <label for="admin_username">管理员用户名</label>
            <input type="text" id="admin_username" name="admin_username" value="<?php echo h($_POST['admin_username'] ?? 'admin'); ?>" required>
        </div>
        <div class="field">
            <label for="admin_display_name">管理员显示名称（可选）</label>
            <input type="text" id="admin_display_name" name="admin_display_name" value="<?php echo h($_POST['admin_display_name'] ?? ''); ?>">
        </div>
        <div class="field">
            <label for="admin_password">管理员密码</label>
            <input type="password" id="admin_password" name="admin_password" required>
        </div>

        <button type="submit">开始安装</button>
    </form>
    <?php endif; ?>

    <p class="notice">安装完成后请删除本文件，并确保 <code>config.php</code> 的权限设置合理。</p>
</div>
</body>
</html>
