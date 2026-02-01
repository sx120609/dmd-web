<?php
$blogPosts = [];
$blogEditPost = null;
$blogFlash = ['type' => '', 'message' => ''];
$blogTabActive = (($_GET['tab'] ?? '') === 'posts');
$blogPostId = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;

$configFile = __DIR__ . '/config.php';
if (file_exists($configFile)) {
    $config = require $configFile;
    if (!empty($config['session_name'])) {
        session_name($config['session_name']);
    }
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $mysqli = @new mysqli(
        $config['db']['host'] ?? '127.0.0.1',
        $config['db']['user'] ?? 'root',
        $config['db']['password'] ?? '',
        $config['db']['database'] ?? '',
        $config['db']['port'] ?? 3306
    );

    if (!$mysqli->connect_errno) {
        if (!empty($config['db']['charset'])) {
            $mysqli->set_charset($config['db']['charset']);
        }
        $mysqli->query(
            "CREATE TABLE IF NOT EXISTS `blog_posts` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(200) NOT NULL,
                `summary` TEXT,
                `content` MEDIUMTEXT NOT NULL,
                `link_url` VARCHAR(500) DEFAULT NULL,
                `published_at` DATE DEFAULT NULL,
                `tags` VARCHAR(255) DEFAULT NULL,
                `author` VARCHAR(120) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $check = $mysqli->query("SHOW COLUMNS FROM `blog_posts` LIKE 'link_url'");
        if ($check instanceof mysqli_result) {
            $hasLink = $check->num_rows > 0;
            $check->free();
            if (!$hasLink) {
                $mysqli->query("ALTER TABLE `blog_posts` ADD COLUMN `link_url` VARCHAR(500) DEFAULT NULL AFTER `content`");
            }
        }
        $check = $mysqli->query("SHOW COLUMNS FROM `blog_posts` LIKE 'published_at'");
        if ($check instanceof mysqli_result) {
            $hasDate = $check->num_rows > 0;
            $check->free();
            if (!$hasDate) {
                $mysqli->query("ALTER TABLE `blog_posts` ADD COLUMN `published_at` DATE DEFAULT NULL AFTER `link_url`");
            }
        }

        $currentUser = null;
        $isAdmin = false;
        if (!empty($_SESSION['user_id'])) {
            $stmt = $mysqli->prepare('SELECT id, username, display_name, role FROM users WHERE id = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('i', $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $currentUser = $result->fetch_assoc() ?: null;
                $stmt->close();
            }
        }
        $isAdmin = $currentUser && ($currentUser['role'] ?? '') === 'admin';
        $isTeacher = $currentUser && ($currentUser['role'] ?? '') === 'teacher';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_action'])) {
            if (!$isAdmin) {
                $blogFlash = ['type' => 'error', 'message' => '没有权限操作文章'];
            } else {
                $action = $_POST['post_action'];
                $title = trim((string) ($_POST['title'] ?? ''));
                $linkUrl = trim((string) ($_POST['link_url'] ?? ''));
                $publishedAt = trim((string) ($_POST['published_at'] ?? ''));
                $author = trim((string) ($_POST['author'] ?? ''));
                $tags = trim((string) ($_POST['tags'] ?? ''));
                $summary = trim((string) ($_POST['summary'] ?? ''));
                $content = '';

                if ($action === 'create') {
                    if ($title === '' || $linkUrl === '') {
                        $blogFlash = ['type' => 'error', 'message' => '标题与链接不能为空'];
                    } else {
                        $stmt = $mysqli->prepare('INSERT INTO blog_posts (title, summary, content, link_url, published_at, tags, author) VALUES (?, ?, ?, ?, ?, ?, ?)');
                        if ($stmt) {
                            $stmt->bind_param('sssssss', $title, $summary, $content, $linkUrl, $publishedAt, $tags, $author);
                            if ($stmt->execute()) {
                                $newId = $stmt->insert_id;
                                $stmt->close();
                                header('Location: /rarelight/admin?tab=posts&post_id=' . $newId . '#posts');
                                exit;
                            }
                            $errorDetail = $stmt->error;
                            $stmt->close();
                            $blogFlash = ['type' => 'error', 'message' => '发布失败：' . $errorDetail];
                            goto blog_posts_done;
                        }
                        $blogFlash = ['type' => 'error', 'message' => '发布失败：' . $mysqli->error];
                    }
                } elseif ($action === 'update') {
                    $postId = (int) ($_POST['post_id'] ?? 0);
                    if ($postId <= 0) {
                        $blogFlash = ['type' => 'error', 'message' => '文章ID无效'];
                    } elseif ($title === '' || $linkUrl === '') {
                        $blogFlash = ['type' => 'error', 'message' => '标题与链接不能为空'];
                    } else {
                        $stmt = $mysqli->prepare('UPDATE blog_posts SET title = ?, summary = ?, content = ?, link_url = ?, published_at = ?, tags = ?, author = ? WHERE id = ?');
                        if ($stmt) {
                            $stmt->bind_param('sssssssi', $title, $summary, $content, $linkUrl, $publishedAt, $tags, $author, $postId);
                            if ($stmt->execute()) {
                                $stmt->close();
                                header('Location: /rarelight/admin?tab=posts&post_id=' . $postId . '#posts');
                                exit;
                            }
                            $errorDetail = $stmt->error;
                            $stmt->close();
                            $blogFlash = ['type' => 'error', 'message' => '保存失败：' . $errorDetail];
                            goto blog_posts_done;
                        }
                        $blogFlash = ['type' => 'error', 'message' => '保存失败：' . $mysqli->error];
                    }
                } elseif ($action === 'delete') {
                    $postId = (int) ($_POST['post_id'] ?? 0);
                    if ($postId > 0) {
                        $stmt = $mysqli->prepare('DELETE FROM blog_posts WHERE id = ? LIMIT 1');
                        if ($stmt) {
                            $stmt->bind_param('i', $postId);
                            $stmt->execute();
                            $stmt->close();
                            header('Location: /rarelight/admin?tab=posts#posts');
                            exit;
                        }
                    }
                    $blogFlash = ['type' => 'error', 'message' => '删除失败'];
                }
            }
        }
        blog_posts_done:

        $postsResult = $mysqli->query('SELECT id, title, summary, link_url, published_at, author, tags, created_at FROM blog_posts ORDER BY COALESCE(published_at, created_at) DESC, id DESC');
        if ($postsResult) {
            while ($row = $postsResult->fetch_assoc()) {
                $row['id'] = (int) $row['id'];
                $blogPosts[] = $row;
            }
            $postsResult->free();
        }

        if ($blogPostId > 0) {
            $stmt = $mysqli->prepare('SELECT id, title, summary, link_url, published_at, author, tags FROM blog_posts WHERE id = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('i', $blogPostId);
                $stmt->execute();
                $result = $stmt->get_result();
                $blogEditPost = $result->fetch_assoc() ?: null;
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>网课系统 · 管理后台</title>
    <link rel="icon" type="image/svg+xml" href="/rarelight/favicon.svg">
    <link rel="shortcut icon" href="/rarelight/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            /* === 核心色盘 === */
            --rl-bg: #f0f4f8;
            /* Slightly darker for better contrast */
            --rl-text-main: #0f172a;
            --rl-text-muted: #64748b;
            --rl-primary: #3b82f6;
            --rl-accent: #8b5cf6;
            --deep-gradient: linear-gradient(135deg, #2563eb, #60a5fa, #22d3ee);
            --gradient-glow: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.05), transparent 70%);

            /* 面板/卡片样式 */
            --glass-bg: rgba(255, 255, 255, 0.9);
            /* More opaque */
            --glass-border: 1px solid rgba(255, 255, 255, 0.8);
            --glass-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.05), 0 8px 10px -6px rgba(59, 130, 246, 0.01);
            /* Colored shadow */
            --header-height: 70px;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Noto Sans SC', system-ui, sans-serif;
            background-color: var(--rl-bg);
            background-image: var(--gradient-glow);
            background-attachment: fixed;
            background-size: 100% 100vh;
            color: var(--rl-text-main);
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
            padding-top: 0;
            padding-bottom: 4rem;
        }

        /* --- 导航栏 --- */
        .site-nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            height: var(--header-height);
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.6);
            display: flex;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }

        .nav-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .brand-mark {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--deep-gradient);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .brand-text {
            line-height: 1.2;
        }

        .brand-text .small {
            font-size: 0.7rem;
            color: var(--rl-text-muted);
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .brand-text .fw-bold {
            font-size: 1rem;
            color: var(--rl-text-main);
        }

        .nav-btn {
            padding: 0.4rem 0.9rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .nav-btn-ghost {
            color: var(--rl-text-muted);
            background: transparent;
        }

        .nav-btn-ghost:hover {
            color: var(--rl-text-main);
            background: rgba(0, 0, 0, 0.04);
        }

        .nav-btn-outline {
            color: var(--rl-text-muted);
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .nav-btn-outline:hover {
            color: var(--rl-primary);
            background: white;
            border-color: var(--rl-primary);
        }

        .nav-btn-primary {
            background: var(--deep-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
            border: 1px solid transparent;
        }

        .nav-btn-primary:hover {
            filter: brightness(1.05);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);
        }

        /* --- 页面容器与面板 --- */
        .page-container {
            padding: 2rem 0 4rem;
        }

        .panel-glass {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 16px;
            box-shadow: var(--glass-shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .panel-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            background: rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .panel-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            color: var(--rl-text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-body {
            padding: 1.5rem;
        }

        /* --- Tabs --- */
        .admin-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .admin-section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--rl-text-main);
            margin-bottom: 0.2rem;
            letter-spacing: -0.02em;
        }

        .admin-tabs {
            display: inline-flex;
            gap: 0.25rem;
            padding: 0.4rem;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 14px;
            backdrop-filter: blur(12px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 0.6rem 1.1rem;
            border-radius: 10px;
            border: 1px solid transparent;
            background: transparent;
            color: var(--rl-text-muted);
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .tab-btn:hover {
            color: var(--rl-text-main);
            background: rgba(255, 255, 255, 0.6);
            transform: translateY(-1px);
        }

        .tab-btn.active {
            background: white;
            color: var(--rl-primary);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1), 0 1px 2px rgba(0, 0, 0, 0.03);
            border-color: rgba(59, 130, 246, 0.1);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
                filter: blur(2px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
                filter: blur(0);
            }
        }

        /* 表单与列表微调 */
        .form-grid {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--rl-text-main);
            margin-bottom: 0.5rem;
            display: block;
            letter-spacing: 0.01em;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
            color: var(--rl-text-main);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
            line-height: 1.5;
        }

        .form-control:hover,
        .form-select:hover {
            border-color: #cbd5e1;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--rl-primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
            transform: translateY(-1px);
        }

        .form-text {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--rl-text-muted);
        }

        .table-list {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .table-list li {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: start;
            transition: all 0.2s;
            border-radius: 8px;
            margin-bottom: 2px;
        }

        .table-list li:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .table-list li:hover {
            background: rgba(255, 255, 255, 0.6);
            transform: scale(0.995);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .table-list li.active {
            background: rgba(59, 130, 246, 0.04);
            border-left: 3px solid var(--rl-primary);
            transform: scale(1);
            box-shadow: none;
        }

        .user-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .user-meta strong {
            font-size: 1rem;
            color: var(--rl-text-main);
            font-weight: 600;
        }

        .user-meta span {
            font-size: 0.85rem;
            color: var(--rl-text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .user-role-tag {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            background: rgba(148, 163, 184, 0.15);
            color: var(--rl-text-muted);
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .user-role-tag.is-admin {
            background: rgba(59, 130, 246, 0.12);
            color: var(--rl-primary);
        }

        .user-role-tag.is-teacher {
            background: rgba(139, 92, 246, 0.12);
            color: var(--rl-accent);
        }

        .list-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .hint {
            font-size: 0.9rem;
            color: var(--rl-text-muted);
            line-height: 1.5;
        }

        .message {
            font-size: 0.9rem;
            margin-top: 0.75rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: inline-block;
        }

        .message.inline {
            padding: 0;
            margin-top: 0.5rem;
            display: block;
        }

        .message.error {
            color: #e11d48;
            background: rgba(225, 29, 72, 0.08);
            border: 1px solid rgba(225, 29, 72, 0.1);
        }

        .message.success {
            color: #059669;
            background: rgba(5, 150, 105, 0.08);
            border: 1px solid rgba(5, 150, 105, 0.1);
        }

        .message.inline.error {
            background: transparent;
            border: none;
            padding: 0;
        }

        .message.inline.success {
            background: transparent;
            border: none;
            padding: 0;
        }
    </style>

</head>

<body class="admin-shell">
    <nav class="site-nav">
        <div class="container-xxl d-flex align-items-center justify-content-between w-100">
            <a href="/rarelight/" class="nav-brand">
                <span class="brand-mark">RL</span>
                <div class="brand-text">
                    <div class="small text-uppercase">RARE LIGHT</div>
                    <div class="fw-bold">管理后台 · Admin</div>
                </div>
            </a>

            <div class="nav-actions d-none d-md-flex align-items-center gap-2">
                <div class="user-chip me-2" id="adminChip" style="display:none"></div>

                <a class="nav-btn nav-btn-ghost" href="/rarelight/">
                    <i class="bi bi-house"></i> 首页
                </a>
                <a class="nav-btn nav-btn-ghost" href="/rarelight/cloud">
                    <i class="bi bi-cloud"></i> 云盘
                </a>
                <button class="nav-btn nav-btn-primary" id="backButton">
                    <i class="bi bi-collection-play-fill me-1"></i> 返回课堂
                </button>
                <button class="nav-btn nav-btn-ghost text-danger" id="logoutButton" title="退出登录">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero removed for cleaner layout -->

    <main class="page-container">
        <div class="container-xxl">
            <!-- Main Glass Panel -->
            <div class="panel-glass">
                <div class="panel-body">

                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <h2 class="fw-bold m-0 text-primary"><i class="bi bi-shield-lock me-2"></i>系统管理中心</h2>
                                <p class="text-muted small mb-0 mt-1">管理用户权限、课程内容及系统设置</p>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <!-- Tabs -->
                        <div class="admin-tabs" role="tablist">
                            <?php if ($isAdmin): ?>
                                <button type="button" class="tab-btn active" data-target="users">
                                    <i class="bi bi-people me-1"></i> 用户管理
                                </button>
                            <?php endif; ?>

                            <button type="button" class="tab-btn <?php echo !$isAdmin ? 'active' : ''; ?>"
                                data-target="courses">
                                <i class="bi bi-collection-play me-1"></i> 课程管理
                            </button>
                            <button type="button" class="tab-btn" data-target="lessons">
                                <i class="bi bi-journal-text me-1"></i> 课节管理
                            </button>

                            <?php if ($isAdmin): ?>
                                <button type="button" class="tab-btn" data-target="assignments">
                                    <i class="bi bi-person-check me-1"></i> 课程分配
                                </button>
                                <button type="button" class="tab-btn" data-target="posts">
                                    <i class="bi bi-pencil-square me-1"></i> 项目日志
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php if ($isAdmin): ?>

                            <div class="tab-content active" id="tab-users" role="tabpanel">
                                <div class="row g-4 align-items-start">
                                    <div class="col-12 col-xl-5 col-xxl-4">
                                        <div class="d-flex flex-column gap-4">
                                            <div class="panel-glass user-list-card h-100">
                                                <div class="panel-header">
                                                    <div>
                                                        <h3 class="panel-title mb-1">用户列表</h3>
                                                        <p class="hint mb-0">点击用户查看详情。</p>
                                                    </div>
                                                </div>
                                                <ul class="table-list user-table flex-grow-1 overflow-auto" id="userList"
                                                    style="max-height: 400px;"></ul>
                                            </div>
                                            <form id="createUserForm" class="panel-glass p-3 form-grid">
                                                <div
                                                    class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                                    <div>
                                                        <h3 class="panel-title mb-1">创建用户</h3>
                                                        <p class="hint mb-0">批量导入或单个创建。</p>
                                                    </div>
                                                    <button type="button" class="nav-btn nav-btn-outline py-1 px-2 small"
                                                        id="openUserImportModal"><i
                                                            class="bi bi-file-earmark-spreadsheet"></i> 批量导入</button>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="newUsername" class="form-label">用户名</label>
                                                    <input id="newUsername" name="username" class="form-control"
                                                        placeholder="例如：student01" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="newDisplayName" class="form-label">显示名称</label>
                                                    <input id="newDisplayName" name="display_name" class="form-control"
                                                        placeholder="学生姓名或昵称">
                                                </div>
                                                <div id="newEntryWrap">
                                                    <div class="mb-3">
                                                        <label for="newEntryYear" class="form-label">入库年份</label>
                                                        <input id="newEntryYear" name="entry_year" class="form-control"
                                                            placeholder="例如：2026 或 26">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="newEntryTerm" class="form-label">入库期次</label>
                                                        <select id="newEntryTerm" name="entry_term" class="form-select">
                                                            <option value="01">春季 · 01</option>
                                                            <option value="02">暑假 · 02</option>
                                                            <option value="03">秋季 · 03</option>
                                                            <option value="04">冬令营 · 04</option>
                                                            <option value="05">特别班 · 05</option>
                                                        </select>
                                                    </div>
                                                    <p class="hint mt-1 mb-3 small">仅学员需要，用于自动生成学号。</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="newPassword" class="form-label">初始密码</label>
                                                    <input id="newPassword" name="password" type="password"
                                                        class="form-control" placeholder="设置登录密码" required>
                                                </div>
                                                <div class="mb-4">
                                                    <label for="newRole" class="form-label">角色</label>
                                                    <select id="newRole" name="role" class="form-select">
                                                        <option value="student">学员</option>
                                                        <option value="teacher">老师</option>
                                                        <option value="admin">管理员</option>
                                                    </select>
                                                </div>
                                                <button type="submit"
                                                    class="nav-btn nav-btn-primary w-100 justify-content-center py-2">创建用户</button>
                                                <div class="message inline" id="createUserMessage" hidden></div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-12 col-xl-7 col-xxl-8">
                                        <section class="panel-glass h-100" id="userDetailCard">
                                            <div class="panel-header">
                                                <div>
                                                    <h3 class="panel-title" id="userDetailTitle">用户详情</h3>
                                                    <p class="hint mb-0" id="userDetailSubtitle">请选择左侧用户</p>
                                                </div>
                                                <span class="rl-badge success" id="userDetailRoleChip" hidden></span>
                                            </div>
                                            <div class="panel-body">
                                                <div class="user-detail-empty" id="userDetailEmpty">
                                                    没有选中的用户，点击左侧列表中的用户即可开始编辑。
                                                </div>
                                                <form id="updateUserForm" class="p-3" hidden>
                                                    <div class="row g-3">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="editUsername" class="form-label">用户名</label>
                                                            <input id="editUsername" class="form-control" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="editDisplayName" class="form-label">显示名称</label>
                                                            <input id="editDisplayName" class="form-control"
                                                                placeholder="学生姓名或昵称">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="editStudentNo" class="form-label">学号</label>
                                                            <input id="editStudentNo" class="form-control" placeholder="未生成"
                                                                readonly>
                                                            <p class="hint mt-1 mb-0 small">学号生成后不可修改。</p>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="editRole" class="form-label">角色</label>
                                                            <select id="editRole" class="form-select">
                                                                <option value="student">学员</option>
                                                                <option value="teacher">老师</option>
                                                                <option value="admin">管理员</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3" id="editEntryWrap">
                                                            <label class="form-label">入库年份 / 期次</label>
                                                            <div class="input-group">
                                                                <input id="editEntryYear" class="form-control"
                                                                    placeholder="例如：2026 或 26">
                                                                <select id="editEntryTerm" class="form-select">
                                                                    <option value="01">春季 · 01</option>
                                                                    <option value="02">暑假 · 02</option>
                                                                    <option value="03">秋季 · 03</option>
                                                                    <option value="04">冬令营 · 04</option>
                                                                    <option value="05">特别班 · 05</option>
                                                                </select>
                                                            </div>
                                                            <p class="hint mt-1 mb-0 small">学号为空时填写，用于补发；留空则不生成。</p>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="editPassword" class="form-label">重置密码</label>
                                                            <div class="input-group">
                                                                <input id="editPassword" type="password"
                                                                    class="form-control" placeholder="新密码">
                                                                <button type="button" class="nav-btn nav-btn-outline"
                                                                    id="resetPasswordButton">生成</button>
                                                            </div>
                                                            <p class="hint mt-1 mb-0 small">留空则不修改。</p>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="d-flex align-items-center gap-3 mt-4 pt-3 border-top border-light">
                                                        <button type="submit"
                                                            class="nav-btn nav-btn-primary px-4">保存修改</button>
                                                        <div class="message inline" id="updateUserMessage" hidden></div>
                                                    </div>
                                                </form>
                                                <div class="danger-zone mt-4 pt-3 border-top border-danger-subtle"
                                                    id="userDangerZone" hidden>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div>
                                                            <strong class="text-danger">危险操作</strong>
                                                            <p class="small text-muted mb-0">删除用户将移除其所有关联数据。</p>
                                                        </div>
                                                        <button type="button"
                                                            class="nav-btn nav-btn-outline text-danger border-danger-subtle"
                                                            id="deleteUserButton"><i class="bi bi-trash"></i> 删除用户</button>
                                                    </div>
                                                    <div class="message inline" id="deleteUserMessage" hidden></div>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="tab-content <?php echo !$isAdmin ? 'active' : ''; ?>" id="tab-courses"
                            role="tabpanel">
                            <div class="row g-4 align-items-start">
                                <div class="col-12 col-xl-5 col-xxl-4">
                                    <form id="createCourseForm" class="panel-glass p-3">
                                        <div class="panel-header ps-0 pe-0 pt-0 bg-transparent border-bottom mb-3">
                                            <h3 class="panel-title mb-1">创建课程</h3>
                                            <p class="hint mb-0">新建一门课程。</p>
                                        </div>
                                        <div class="mb-3">
                                            <label for="courseTitleInput" class="form-label">课程名称</label>
                                            <input id="courseTitleInput" name="title" class="form-control"
                                                placeholder="例如：高等数学" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="courseInstructorInput" class="form-label">讲师/老师</label>
                                            <input id="courseInstructorInput" name="instructor" class="form-control"
                                                placeholder="可选，填写讲师或负责人">
                                        </div>
                                        <div class="mb-3">
                                            <label for="courseTagsInput" class="form-label">标签</label>
                                            <input id="courseTagsInput" name="tags" class="form-control"
                                                placeholder="用逗号分隔，例如：数学,基础,直播">
                                        </div>
                                        <div class="mb-4">
                                            <label for="courseDescriptionInput" class="form-label">课程简介</label>
                                            <textarea id="courseDescriptionInput" name="description"
                                                class="form-control" rows="4" placeholder="补充课程概述与亮点"></textarea>
                                        </div>
                                        <button type="submit"
                                            class="nav-btn nav-btn-primary w-100 justify-content-center py-2">创建课程</button>
                                        <div class="message inline" id="createCourseMessage" hidden></div>
                                    </form>
                                </div>
                                <div class="col-12 col-xl-7 col-xxl-8">
                                    <div class="panel-glass h-100">
                                        <div class="panel-header">
                                            <div>
                                                <h3 class="panel-title mb-1">课程列表</h3>
                                                <p class="hint mb-0">点击课程编辑，删除将移除关联课节。</p>
                                            </div>
                                        </div>
                                        <ul class="table-list flex-grow-1 overflow-auto" id="courseList"
                                            style="max-height: 500px;"></ul>
                                        <div class="message inline px-3" id="courseListMessage" hidden></div>

                                        <form id="updateCourseForm" class="p-3 border-top border-light" hidden>
                                            <h3 class="panel-title mb-3">编辑课程</h3>
                                            <div class="mb-3">
                                                <label for="editCourseTitle" class="form-label">课程名称</label>
                                                <input id="editCourseTitle" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editCourseInstructor" class="form-label">讲师/老师</label>
                                                <input id="editCourseInstructor" class="form-control"
                                                    placeholder="可选，填写讲师或负责人">
                                            </div>
                                            <div class="mb-3">
                                                <label for="editCourseTags" class="form-label">标签</label>
                                                <input id="editCourseTags" class="form-control" placeholder="用逗号分隔标签">
                                            </div>
                                            <div class="mb-4">
                                                <label for="editCourseDescription" class="form-label">课程简介</label>
                                                <textarea id="editCourseDescription" class="form-control" rows="4"
                                                    placeholder="补充课程概述"></textarea>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit"
                                                    class="nav-btn nav-btn-primary flex-fill justify-content-center py-2">保存修改</button>
                                                <button type="button"
                                                    class="nav-btn nav-btn-ghost flex-fill justify-content-center py-2"
                                                    id="cancelCourseEdit">取消</button>
                                            </div>
                                            <div class="message inline" id="updateCourseMessage" hidden></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-content" id="tab-lessons" role="tabpanel">
                            <div class="row g-4 align-items-start">
                                <div class="col-12 col-xl-5 col-xxl-4">
                                    <form id="createLessonForm" class="panel-glass p-3">
                                        <div class="panel-header ps-0 pe-0 pt-0 bg-transparent border-bottom mb-3">
                                            <h3 class="panel-title mb-1">创建课节</h3>
                                            <p class="hint mb-0">为课程添加章节。</p>
                                        </div>
                                        <div class="mb-3">
                                            <label for="lessonCourseSelect" class="form-label">所属课程</label>
                                            <select id="lessonCourseSelect" name="course_id" class="form-select"
                                                required></select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="lessonTitleInput" class="form-label">课节标题</label>
                                            <input id="lessonTitleInput" name="title" class="form-control"
                                                placeholder="例如：第一章 函数极限" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="lessonVideoInput" class="form-label">视频地址</label>
                                            <div class="input-group">
                                                <input id="lessonVideoInput" name="video_url" class="form-control"
                                                    placeholder="支持哔哩哔哩链接或本地视频文件路径">
                                                <button type="button"
                                                    class="btn btn-outline-secondary cloud-picker-button"
                                                    data-target-input="lessonVideoInput">云盘选择</button>
                                            </div>
                                            <p class="hint mt-1 mb-0 small">可直接粘贴外部视频地址，或点击云盘选择已有文件。</p>
                                        </div>
                                        <div class="mb-3">
                                            <label for="lessonAttachmentsInput"
                                                class="form-label">附件（每行“名称|链接”或直接粘贴链接）</label>
                                            <div class="d-flex flex-column gap-2">
                                                <textarea id="lessonAttachmentsInput" name="attachments"
                                                    class="form-control" rows="3" placeholder="示例：
讲义|https://example.com/file.pdf
练习|https://example.com/ex.pdf"></textarea>
                                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                                    <button type="button"
                                                        class="btn btn-outline-secondary btn-sm cloud-picker-button"
                                                        data-target-input="lessonAttachmentsInput"
                                                        data-cloud-mode="attachment">云盘选择</button>
                                                    <p class="hint mb-0 small">支持外部链接或云盘外链，留空则无附件。</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label for="lessonDescriptionInput" class="form-label">课节简介</label>
                                            <textarea id="lessonDescriptionInput" name="description"
                                                class="form-control" rows="4" placeholder="填写课节要点"></textarea>
                                        </div>
                                        <button type="submit"
                                            class="nav-btn nav-btn-primary w-100 justify-content-center py-2">创建课节</button>
                                        <div class="message inline" id="createLessonMessage" hidden></div>
                                    </form>
                                </div>
                                <div class="col-12 col-xl-7 col-xxl-8">
                                    <div class="panel-glass h-100">
                                        <div class="panel-header">
                                            <div>
                                                <h3 class="panel-title mb-1">课节列表</h3>
                                                <p class="hint mb-0">选择课程查看课节。</p>
                                            </div>
                                        </div>
                                        <ul class="table-list flex-grow-1 overflow-auto" id="lessonList"
                                            style="max-height: 500px;"></ul>
                                        <div class="message inline px-3" id="lessonListMessage" hidden></div>

                                        <form id="updateLessonForm" class="p-3 border-top border-light" hidden>
                                            <h3 class="panel-title mb-3">编辑课节</h3>
                                            <div class="mb-3">
                                                <label for="editLessonCourseSelect" class="form-label">所属课程</label>
                                                <select id="editLessonCourseSelect" class="form-select"
                                                    required></select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editLessonTitle" class="form-label">课节标题</label>
                                                <input id="editLessonTitle" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editLessonVideo" class="form-label">视频地址</label>
                                                <div class="input-group">
                                                    <input id="editLessonVideo" class="form-control"
                                                        placeholder="支持哔哩哔哩链接或本地视频文件路径">
                                                    <button type="button"
                                                        class="btn btn-outline-secondary cloud-picker-button"
                                                        data-target-input="editLessonVideo">云盘选择</button>
                                                </div>
                                                <p class="hint mt-1 mb-0 small">可直接粘贴外部视频地址，或点击云盘选择已有文件。</p>
                                            </div>
                                            <div class="mb-3">
                                                <label for="editLessonAttachments"
                                                    class="form-label">附件（每行“名称|链接”或直接粘贴链接）</label>
                                                <div class="d-flex flex-column gap-2">
                                                    <textarea id="editLessonAttachments" class="form-control" rows="3"
                                                        placeholder="示例：
讲义 PDF|https://example.com/file.pdf
练习题|https://example.com/ex.pdf"></textarea>
                                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm cloud-picker-button"
                                                            data-target-input="editLessonAttachments"
                                                            data-cloud-mode="attachment">云盘选择</button>
                                                        <p class="hint mb-0 small">支持外部链接或云盘外链。</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label for="editLessonDescription" class="form-label">课节简介</label>
                                                <textarea id="editLessonDescription" class="form-control" rows="4"
                                                    placeholder="填写课节要点"></textarea>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit"
                                                    class="nav-btn nav-btn-primary flex-fill justify-content-center py-2">保存修改</button>
                                                <button type="button"
                                                    class="nav-btn nav-btn-ghost flex-fill justify-content-center py-2"
                                                    id="cancelLessonEdit">取消</button>
                                            </div>
                                            <div class="message inline" id="updateLessonMessage" hidden></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($isAdmin): ?>
                            <div class="tab-content" id="tab-assignments" role="tabpanel">
                                <div class="row g-4 align-items-start">
                                    <div class="col-12 col-xl-5 col-xxl-4">
                                        <form id="assignCourseForm" class="panel-glass p-3">
                                            <div
                                                class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                                <div>
                                                    <h3 class="panel-title mb-1">分配课程</h3>
                                                    <p class="hint mb-0">批量或单个分配。</p>
                                                </div>
                                                <button type="button" class="nav-btn nav-btn-outline py-1 px-2 small"
                                                    id="openAssignImportModal"><i
                                                        class="bi bi-file-earmark-spreadsheet"></i> 批量分配</button>
                                            </div>
                                            <div class="mb-3">
                                                <label for="assignUserSelect" class="form-label">选择用户</label>
                                                <select id="assignUserSelect" name="user_id" class="form-select"
                                                    required></select>
                                            </div>
                                            <div class="mb-4">
                                                <label for="assignCourseSelect" class="form-label">分配课程</label>
                                                <select id="assignCourseSelect" name="course_id" class="form-select"
                                                    required></select>
                                            </div>
                                            <button type="submit"
                                                class="nav-btn nav-btn-primary w-100 justify-content-center py-2">分配课程</button>
                                            <div class="message inline" id="assignCourseMessage" hidden></div>
                                        </form>
                                    </div>
                                    <div class="col-12 col-xl-7 col-xxl-8">
                                        <div class="panel-glass h-100">
                                            <div class="panel-header">
                                                <div>
                                                    <h3 class="panel-title mb-1">用户已分配课程</h3>
                                                    <p class="hint mb-0">选择用户显示已分配课程。</p>
                                                </div>
                                            </div>
                                            <ul class="table-list flex-grow-1 overflow-auto" id="assignmentList"
                                                style="max-height: 500px;"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($isAdmin): ?>
                            <div class="tab-content" id="tab-posts" role="tabpanel">
                                <div class="row g-4 align-items-start">
                                    <div class="col-12 col-xl-5 col-xxl-4">
                                        <form id="createPostForm" class="panel-glass p-3" method="post"
                                            action="/rarelight/admin?tab=posts#posts">
                                            <input type="hidden" name="post_action" value="create">

                                            <div class="panel-header ps-0 pe-0 pt-0 bg-transparent border-bottom mb-3">
                                                <h3 class="panel-title mb-1">发布文章</h3>
                                                <p class="hint mb-0">发布项目日志或新闻。</p>
                                            </div>

                                            <div class="mb-3">
                                                <label for="postTitleInput" class="form-label">文章标题</label>
                                                <input id="postTitleInput" name="title" class="form-control"
                                                    placeholder="例如：阶段成果总结" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="postLinkInput" class="form-label">公众号文章链接</label>
                                                <input id="postLinkInput" name="link_url" class="form-control"
                                                    placeholder="https://mp.weixin.qq.com/..." required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="postDateInput" class="form-label">发布日期</label>
                                                <input id="postDateInput" name="published_at" type="date"
                                                    class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label for="postAuthorInput" class="form-label">作者/负责人</label>
                                                <input id="postAuthorInput" name="author" class="form-control"
                                                    placeholder="可选，填写负责人">
                                            </div>
                                            <div class="mb-3">
                                                <label for="postTagsInput" class="form-label">标签</label>
                                                <input id="postTagsInput" name="tags" class="form-control"
                                                    placeholder="用逗号分隔，例如：调研,里程碑">
                                            </div>
                                            <div class="mb-4">
                                                <label for="postSummaryInput" class="form-label">摘要</label>
                                                <textarea id="postSummaryInput" name="summary" class="form-control" rows="3"
                                                    placeholder="简要概述（可选）"></textarea>
                                            </div>
                                            <button type="submit"
                                                class="nav-btn nav-btn-primary w-100 justify-content-center py-2">发布文章</button>
                                            <div class="message inline <?php echo $blogFlash['type'] === 'error' ? 'text-danger' : ($blogFlash['type'] === 'success' ? 'text-success' : ''); ?>"
                                                id="createPostMessage" <?php echo $blogFlash['message'] ? '' : 'hidden'; ?>>
                                                <?php echo htmlspecialchars($blogFlash['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-12 col-xl-7 col-xxl-8">
                                        <div class="panel-glass h-100">
                                            <div class="panel-header">
                                                <div>
                                                    <h3 class="panel-title mb-1">文章列表</h3>
                                                    <p class="hint mb-0">点击文章可编辑，删除不可恢复。</p>
                                                </div>
                                            </div>
                                            <ul class="table-list flex-grow-1 overflow-auto" id="postList"
                                                style="max-height: 500px;">
                                                <?php if (empty($blogPosts)): ?>
                                                    <li class="text-muted p-3">暂无文章，请先发布。</li>
                                                <?php else: ?>
                                                    <?php foreach ($blogPosts as $post): ?>
                                                        <li
                                                            class="selectable<?php echo ($blogEditPost && (int) $blogEditPost['id'] === (int) $post['id']) ? ' active' : ''; ?>">
                                                            <div style="flex: 1;">
                                                                <strong><?php echo htmlspecialchars($post['title'] ?? ('文章 ' . $post['id']), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                                <div class="text-muted" style="font-size: 0.85rem;">
                                                                    <?php
                                                                    $metaPieces = [];
                                                                    if (!empty($post['author'])) {
                                                                        $metaPieces[] = htmlspecialchars($post['author'], ENT_QUOTES, 'UTF-8');
                                                                    }
                                                                    if (!empty($post['published_at'])) {
                                                                        $metaPieces[] = htmlspecialchars($post['published_at'], ENT_QUOTES, 'UTF-8');
                                                                    }
                                                                    $summary = trim((string) ($post['summary'] ?? ''));
                                                                    if ($summary !== '') {
                                                                        $metaPieces[] = mb_strimwidth($summary, 0, 60, '…', 'UTF-8');
                                                                    }
                                                                    $metaText = $metaPieces ? implode(' · ', $metaPieces) : '';
                                                                    ?>
                                                                    <?php echo '文章ID：' . (int) $post['id'] . ($metaText ? ' · ' . htmlspecialchars($metaText, ENT_QUOTES, 'UTF-8') : ''); ?>
                                                                </div>
                                                            </div>
                                                            <div class="list-actions"
                                                                style="display: flex; gap: 0.5rem; align-items: center;">
                                                                <button type="button"
                                                                    class="nav-btn nav-btn-outline py-1 px-2 small edit-post-btn"
                                                                    data-post='<?php echo json_encode($post, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>
                                                                <form method="post" action="/rarelight/admin?tab=posts#posts"
                                                                    onsubmit="return confirm('确定删除该文章？');">
                                                                    <input type="hidden" name="post_action" value="delete">
                                                                    <input type="hidden" name="post_id"
                                                                        value="<?php echo (int) $post['id']; ?>">
                                                                    <button type="submit"
                                                                        class="nav-btn nav-btn-outline text-danger border-danger-subtle py-1 px-2 small"><i
                                                                            class="bi bi-trash"></i></button>
                                                                </form>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </ul>
                                            <div class="message inline" id="postListMessage" hidden></div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
    </main>
    <!-- 批量导入用户弹窗（独立挂载到 body 防止被父容器遮挡） -->
    <!-- 批量导入用户弹窗 -->
    <div class="modal fade" id="userImportModal" tabindex="-1" aria-labelledby="userImportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="panel-glass modal-content border-0">
                <div class="modal-header border-bottom border-light">
                    <h5 class="modal-title" id="userImportModalLabel">批量导入用户</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="hint">下载模板 CSV，填写后上传。字段：username, display_name, password, role，学员可补充 entry_year,
                        entry_term（或 student_no）。</p>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                        <button type="button" class="nav-btn nav-btn-outline py-1 px-2 small"
                            id="downloadUserTemplate">下载模板</button>
                        <small class="text-secondary">文件大小限制 5MB</small>
                    </div>
                    <div class="mb-3">
                        <label for="userImportFile" class="form-label">上传填写好的 CSV</label>
                        <input id="userImportFile" type="file" accept=".csv,text/csv" class="form-control">
                    </div>
                    <div class="message inline" id="userImportMessage" hidden></div>
                </div>
                <div class="modal-footer border-top border-light">
                    <button type="button" class="nav-btn nav-btn-ghost" data-bs-dismiss="modal">关闭</button>
                    <button type="button" class="nav-btn nav-btn-primary" id="userImportButton">导入用户</button>
                </div>
            </div>
        </div>
    </div>
    <!-- 批量分配课程弹窗 -->
    <!-- 批量分配课程弹窗 -->
    <div class="modal fade" id="assignImportModal" tabindex="-1" aria-labelledby="assignImportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="panel-glass modal-content border-0">
                <div class="modal-header border-bottom border-light">
                    <h5 class="modal-title" id="assignImportModalLabel">批量分配课程</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="hint">下载模板 CSV，填写后上传。字段：username, course_id。</p>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                        <button type="button" class="nav-btn nav-btn-outline py-1 px-2 small"
                            id="downloadAssignTemplate">下载模板</button>
                        <small class="text-secondary">文件大小限制 5MB</small>
                    </div>
                    <div class="mb-3">
                        <label for="assignImportFile" class="form-label">上传填写好的 CSV</label>
                        <input id="assignImportFile" type="file" accept=".csv,text/csv" class="form-control">
                    </div>
                    <div class="message inline" id="assignImportMessage" hidden></div>
                </div>
                <div class="modal-footer border-top border-light">
                    <button type="button" class="nav-btn nav-btn-ghost" data-bs-dismiss="modal">关闭</button>
                    <button type="button" class="nav-btn nav-btn-primary" id="assignImportButton">导入分配</button>
                </div>
            </div>
        </div>
    </div>
    <!-- 云盘选择弹窗 -->
    <div class="modal fade" id="cloudPickerModal" tabindex="-1" aria-labelledby="cloudPickerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="panel-glass modal-content border-0">
                <div class="modal-header border-bottom border-light">
                    <h5 class="modal-title" id="cloudPickerModalLabel">从云盘选择文件</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="message inline" id="cloudPickerMessage" hidden></div>
                    <div class="mb-3">
                        <label class="form-label">上传文件到云盘（自动开启外链）</label>
                        <div class="input-group">
                            <input class="form-control" type="file" id="cloudUploadInput" multiple>
                            <button class="nav-btn nav-btn-outline" type="button" id="cloudUploadButton">上传</button>
                        </div>
                        <div class="small text-secondary mt-1" id="cloudUploadHint">支持批量上传，单文件上限 2GB。</div>
                        <div class="message mt-2 inline" id="cloudUploadMessage" hidden></div>
                        <div class="progress mt-2" style="height: 10px;" hidden id="cloudUploadProgressWrap">
                            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0"
                                aria-valuemin="0" aria-valuemax="100" id="cloudUploadProgressBar"></div>
                        </div>
                        <div class="small text-secondary mt-1" id="cloudUploadProgressText"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="background: transparent;">
                            <thead class="table-light">
                                <tr>
                                    <th
                                        style="background: transparent; border-bottom: 1px solid rgba(255,255,255,0.1);">
                                        文件名</th>
                                    <th
                                        style="background: transparent; border-bottom: 1px solid rgba(255,255,255,0.1);">
                                        大小</th>
                                    <th
                                        style="background: transparent; border-bottom: 1px solid rgba(255,255,255,0.1);">
                                        状态</th>
                                    <th class="text-end"
                                        style="background: transparent; border-bottom: 1px solid rgba(255,255,255,0.1);">
                                        操作</th>
                                </tr>
                            </thead>
                            <tbody id="cloudPickerBody">
                                <tr>
                                    <td colspan="4" class="text-secondary text-center py-4">正在加载...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top border-light">
                    <button type="button" class="nav-btn nav-btn-ghost" data-bs-dismiss="modal">关闭</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 全局确认弹窗 -->
    <!-- 全局确认弹窗 -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="panel-glass modal-content border-0">
                <div class="modal-header border-bottom border-light">
                    <h5 class="modal-title" id="confirmModalLabel">确认操作</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmModalBody">确定执行该操作吗？</div>
                <div class="modal-footer border-top border-light">
                    <button type="button" class="nav-btn nav-btn-ghost" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="nav-btn nav-btn-primary" id="confirmModalConfirm">确定</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div class="modal fade" id="editCourseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content panel-glass border-0">
                <div class="modal-header border-light">
                    <h5 class="modal-title fw-bold">编辑课程</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editCourseFormModal">
                        <input type="hidden" id="modalEditCourseId" name="id">
                        <div class="mb-3">
                            <label for="modalEditCourseTitle" class="form-label">课程名称</label>
                            <input id="modalEditCourseTitle" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="modalEditCourseInstructor" class="form-label">讲师/老师</label>
                            <input id="modalEditCourseInstructor" name="instructor" class="form-control"
                                placeholder="可选，填写讲师或负责人">
                        </div>
                        <div class="mb-3">
                            <label for="modalEditCourseTags" class="form-label">标签</label>
                            <input id="modalEditCourseTags" name="tags" class="form-control" placeholder="用逗号分隔">
                        </div>
                        <div class="mb-3">
                            <label for="modalEditCourseDescription" class="form-label">课程简介</label>
                            <textarea id="modalEditCourseDescription" name="description" class="form-control"
                                rows="4"></textarea>
                        </div>
                        <div class="message inline" id="editCourseModalMessage" hidden></div>
                    </form>
                </div>
                <div class="modal-footer border-light">
                    <button type="button" class="nav-btn nav-btn-ghost" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="nav-btn nav-btn-primary px-4" id="submitEditCourseModal">保存修改</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Lesson Modal -->
    <div class="modal fade" id="editLessonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content panel-glass border-0">
                <div class="modal-header border-light">
                    <h5 class="modal-title fw-bold">编辑课节</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editLessonFormModal">
                        <input type="hidden" id="modalEditLessonId" name="lesson_id">
                        <div class="mb-3">
                            <label for="modalEditLessonCourseSelect" class="form-label">所属课程</label>
                            <select id="modalEditLessonCourseSelect" name="course_id" class="form-select"
                                required></select>
                        </div>
                        <div class="mb-3">
                            <label for="modalEditLessonTitle" class="form-label">课节标题</label>
                            <input id="modalEditLessonTitle" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="modalEditLessonVideo" class="form-label">视频地址</label>
                            <div class="input-group">
                                <input id="modalEditLessonVideo" name="video_url" class="form-control"
                                    placeholder="支持哔哩哔哩链接或本地视频文件路径">
                                <button type="button" class="btn btn-outline-secondary cloud-picker-button"
                                    data-target-input="modalEditLessonVideo">云盘选择</button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="modalEditLessonAttachments" class="form-label">附件</label>
                            <div class="d-flex flex-column gap-2">
                                <textarea id="modalEditLessonAttachments" name="attachments" class="form-control"
                                    rows="3" placeholder="格式：名称|链接"></textarea>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <button type="button" class="btn btn-outline-secondary btn-sm cloud-picker-button"
                                        data-target-input="modalEditLessonAttachments"
                                        data-cloud-mode="attachment">云盘选择</button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="modalEditLessonDescription" class="form-label">课节简介</label>
                            <textarea id="modalEditLessonDescription" name="description" class="form-control"
                                rows="4"></textarea>
                        </div>
                        <div class="message inline" id="editLessonModalMessage" hidden></div>
                    </form>
                </div>
                <div class="modal-footer border-light">
                    <button type="button" class="nav-btn nav-btn-ghost" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="nav-btn nav-btn-primary px-4" id="submitEditLessonModal">保存修改</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Post Modal -->
    <div class="modal fade" id="editPostModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content panel-glass border-0">
                <div class="modal-header border-light">
                    <h5 class="modal-title fw-bold">编辑文章</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editPostFormModal" method="post" action="/rarelight/admin?tab=posts#posts">
                        <input type="hidden" name="post_action" value="update">
                        <input type="hidden" id="modalEditPostId" name="post_id">
                        <div class="mb-3">
                            <label for="modalEditPostTitle" class="form-label">文章标题</label>
                            <input id="modalEditPostTitle" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="modalEditPostLink" class="form-label">公众号文章链接</label>
                            <input id="modalEditPostLink" name="link_url" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="modalEditPostDate" class="form-label">发布日期</label>
                            <input id="modalEditPostDate" name="published_at" type="date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="modalEditPostAuthor" class="form-label">作者/负责人</label>
                            <input id="modalEditPostAuthor" name="author" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="modalEditPostTags" class="form-label">标签</label>
                            <input id="modalEditPostTags" name="tags" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="modalEditPostSummary" class="form-label">摘要</label>
                            <textarea id="modalEditPostSummary" name="summary" class="form-control" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-light">
                    <button type="button" class="nav-btn nav-btn-ghost" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="nav-btn nav-btn-primary px-4" id="submitEditPostModal">保存修改</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        const BASE_PATH = '/rarelight';
        const API_BASE = `${BASE_PATH}/api`;
        const ROUTE_LOGIN = `${BASE_PATH}/login`;
        const ROUTE_DASHBOARD = `${BASE_PATH}/dashboard`;
        const FILES_ENDPOINT = `${API_BASE}/files.php`;

        function normalizeApiUrl(url) {
            if (url.startsWith(`${API_BASE}/`)) {
                const [path, query] = url.split('?');
                const sanitizedPath = path.replace(/\/{2,}/g, '/');
                return query ? `${sanitizedPath}?${query}` : sanitizedPath;
            }
            return url;
        }

        function withBasePath(path = '') {
            if (!path) return '';
            if (/^https?:\/\//i.test(path)) return path;
            const normalized = path.startsWith('/') ? path : `/${path}`;
            if (normalized.startsWith(`${BASE_PATH}/`)) return normalized;
            return `${BASE_PATH}${normalized}`.replace(/\/{2,}/g, '/');
        }

        function buildAbsoluteWithCurrentOrigin(path = '') {
            if (!path) return '';
            try {
                const url = new URL(path, window.location.origin);
                // Force path to stay under BASE_PATH when applicable
                const normalizedPath = withBasePath(url.pathname + url.search + url.hash);
                return `${window.location.origin}${normalizedPath}`;
            } catch (error) {
                return withBasePath(path);
            }
        }

        const logoutButton = document.getElementById('logoutButton');
        const backButton = document.getElementById('backButton');
        const adminChip = document.getElementById('adminChip');
        const cloudPickerModalEl = document.getElementById('cloudPickerModal');
        const cloudPickerBody = document.getElementById('cloudPickerBody');
        const cloudPickerMessage = document.getElementById('cloudPickerMessage');
        const cloudUploadInput = document.getElementById('cloudUploadInput');
        const cloudUploadButton = document.getElementById('cloudUploadButton');
        const cloudUploadMessage = document.getElementById('cloudUploadMessage');
        const cloudUploadProgressWrap = document.getElementById('cloudUploadProgressWrap');
        const cloudUploadProgressBar = document.getElementById('cloudUploadProgressBar');
        const cloudUploadProgressText = document.getElementById('cloudUploadProgressText');

        const tabButtons = document.querySelectorAll('.admin-tabs button');
        const tabContents = document.querySelectorAll('.tab-content');

        const createUserForm = document.getElementById('createUserForm');
        const createUserMessage = document.getElementById('createUserMessage');
        const newRoleSelect = document.getElementById('newRole');
        const newEntryWrap = document.getElementById('newEntryWrap');
        const newEntryYearInput = document.getElementById('newEntryYear');
        const newEntryTermSelect = document.getElementById('newEntryTerm');
        const downloadUserTemplateButton = document.getElementById('downloadUserTemplate');
        const downloadAssignTemplateButton = document.getElementById('downloadAssignTemplate');
        const userImportFileInput = document.getElementById('userImportFile');
        const userImportButton = document.getElementById('userImportButton');
        const userImportMessage = document.getElementById('userImportMessage');
        const userImportModalEl = document.getElementById('userImportModal');
        const assignImportFileInput = document.getElementById('assignImportFile');
        const assignImportButton = document.getElementById('assignImportButton');
        const assignImportMessage = document.getElementById('assignImportMessage');
        const assignImportModalEl = document.getElementById('assignImportModal');
        let userImportModal = null;
        let assignImportModal = null;
        const userListEl = document.getElementById('userList');
        const updateUserForm = document.getElementById('updateUserForm');
        const updateUserMessage = document.getElementById('updateUserMessage');
        const editUsernameInput = document.getElementById('editUsername');
        const editDisplayNameInput = document.getElementById('editDisplayName');
        const editStudentNoInput = document.getElementById('editStudentNo');
        const editRoleSelect = document.getElementById('editRole');
        const editPasswordInput = document.getElementById('editPassword');
        const editEntryWrap = document.getElementById('editEntryWrap');
        const editEntryYearInput = document.getElementById('editEntryYear');
        const editEntryTermSelect = document.getElementById('editEntryTerm');
        const resetPasswordButton = document.getElementById('resetPasswordButton');
        const deleteUserButton = document.getElementById('deleteUserButton');
        const deleteUserMessage = document.getElementById('deleteUserMessage');
        const openUserImportModalButton = document.getElementById('openUserImportModal');
        const openAssignImportModalButton = document.getElementById('openAssignImportModal');
        const userDetailTitle = document.getElementById('userDetailTitle');
        const userDetailSubtitle = document.getElementById('userDetailSubtitle');
        const userDetailRoleChip = document.getElementById('userDetailRoleChip');
        const userDetailEmpty = document.getElementById('userDetailEmpty');
        const userDangerZone = document.getElementById('userDangerZone');

        if (resetPasswordButton) {
            resetPasswordButton.disabled = true;
        }
        if (deleteUserButton) {
            deleteUserButton.disabled = true;
        }
        if (newRoleSelect) {
            toggleStudentEntryFields(newRoleSelect.value);
            newRoleSelect.addEventListener('change', () => {
                toggleStudentEntryFields(newRoleSelect.value);
            });
        }
        if (editRoleSelect) {
            editRoleSelect.addEventListener('change', () => {
                const isStudent = editRoleSelect.value === 'student';
                const hasStudentNo = Boolean(editStudentNoInput && editStudentNoInput.value);
                if (editEntryWrap) {
                    editEntryWrap.hidden = !isStudent || hasStudentNo;
                }
                if (editEntryYearInput) {
                    editEntryYearInput.value = '';
                }
                if (editEntryTermSelect) {
                    editEntryTermSelect.value = getDefaultEntryTerm();
                }
            });
        }
        const createCourseForm = document.getElementById('createCourseForm');
        const createCourseMessage = document.getElementById('createCourseMessage');
        const courseInstructorInput = document.getElementById('courseInstructorInput');
        const courseTagsInput = document.getElementById('courseTagsInput');
        const courseListEl = document.getElementById('courseList');
        const courseListMessage = document.getElementById('courseListMessage');
        const updateCourseMessage = document.getElementById('editCourseModalMessage'); // Reused ID mapping for less JS change or new var
        // Modals
        let editCourseModal = null;
        let editLessonModal = null;
        let editPostModal = null;

        const editCourseModalEl = document.getElementById('editCourseModal');
        const editLessonModalEl = document.getElementById('editLessonModal');
        const editPostModalEl = document.getElementById('editPostModal');

        const editCourseFormModal = document.getElementById('editCourseFormModal');
        const submitEditCourseModalBtn = document.getElementById('submitEditCourseModal');
        const modalEditCourseId = document.getElementById('modalEditCourseId');
        const modalEditCourseTitle = document.getElementById('modalEditCourseTitle');
        const modalEditCourseInstructor = document.getElementById('modalEditCourseInstructor');
        const modalEditCourseTags = document.getElementById('modalEditCourseTags');
        const modalEditCourseDescription = document.getElementById('modalEditCourseDescription');
        const editCourseModalMessage = document.getElementById('editCourseModalMessage');

        const editLessonFormModal = document.getElementById('editLessonFormModal');
        const submitEditLessonModalBtn = document.getElementById('submitEditLessonModal');
        const modalEditLessonId = document.getElementById('modalEditLessonId');
        const modalEditLessonCourseSelect = document.getElementById('modalEditLessonCourseSelect');
        const modalEditLessonTitle = document.getElementById('modalEditLessonTitle');
        const modalEditLessonVideo = document.getElementById('modalEditLessonVideo');
        const modalEditLessonAttachments = document.getElementById('modalEditLessonAttachments');
        const modalEditLessonDescription = document.getElementById('modalEditLessonDescription');
        const editLessonModalMessage = document.getElementById('editLessonModalMessage');

        const editPostFormModal = document.getElementById('editPostFormModal');
        const submitEditPostModalBtn = document.getElementById('submitEditPostModal');
        const modalEditPostId = document.getElementById('modalEditPostId');
        const modalEditPostTitle = document.getElementById('modalEditPostTitle');
        const modalEditPostLink = document.getElementById('modalEditPostLink');
        const modalEditPostDate = document.getElementById('modalEditPostDate');
        const modalEditPostAuthor = document.getElementById('modalEditPostAuthor');
        const modalEditPostTags = document.getElementById('modalEditPostTags');
        const modalEditPostSummary = document.getElementById('modalEditPostSummary');

        const createLessonForm = document.getElementById('createLessonForm');
        const createLessonMessage = document.getElementById('createLessonMessage');
        const lessonCourseSelect = document.getElementById('lessonCourseSelect');
        const lessonTitleInput = document.getElementById('lessonTitleInput');
        const lessonVideoInput = document.getElementById('lessonVideoInput');
        const lessonAttachmentsInput = document.getElementById('lessonAttachmentsInput');
        const lessonDescriptionInput = document.getElementById('lessonDescriptionInput');
        const lessonListEl = document.getElementById('lessonList');
        const lessonListMessage = document.getElementById('lessonListMessage');


        const createPostForm = document.getElementById('createPostForm');
        const createPostMessage = document.getElementById('createPostMessage');
        const postListEl = document.getElementById('postList');



        const assignCourseForm = document.getElementById('assignCourseForm');
        const assignCourseMessage = document.getElementById('assignCourseMessage');
        const assignUserSelect = document.getElementById('assignUserSelect');
        const assignCourseSelect = document.getElementById('assignCourseSelect');
        const assignmentListEl = document.getElementById('assignmentList');
        let cloudPickerModal = null;
        let cloudFiles = [];
        let activeCloudTargetInputId = null;
        let activeCloudTargetMode = 'default';
        const confirmModalEl = document.getElementById('confirmModal');
        const confirmModalBody = document.getElementById('confirmModalBody');
        const confirmModalConfirm = document.getElementById('confirmModalConfirm');
        let confirmModal = null;

        let state = {
            users: [],
            courses: [],
            lessons: {},
            currentUser: null,
            selectedUserId: null,
            selectedCourseId: null,
            selectedLessonId: null,
            editingLessonOriginalCourseId: null
        };

        function setMessage(element, text = '', type = '') {
            if (!element) {
                return;
            }
            const message = text || '';
            element.textContent = message;
            element.classList.remove('error', 'success');
            const hasText = Boolean(message);
            element.hidden = !hasText;
            if (hasText && type) {
                element.classList.add(type);
            }
        }

        function formatSize(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.min(units.length - 1, Math.floor(Math.log(bytes) / Math.log(1024)));
            const size = bytes / Math.pow(1024, i);
            return `${size.toFixed(size >= 10 || i === 0 ? 0 : 1)} ${units[i]}`;
        }

        function showConfirm(message = '确定执行该操作吗？', title = '确认操作') {
            return new Promise((resolve) => {
                if (!confirmModalEl || !confirmModalBody || !confirmModalConfirm || typeof bootstrap === 'undefined') {
                    const fallback = window.confirm(message);
                    resolve(fallback);
                    return;
                }
                if (!confirmModal) {
                    confirmModal = new bootstrap.Modal(confirmModalEl);
                }
                const titleEl = document.getElementById('confirmModalLabel');
                if (titleEl) {
                    titleEl.textContent = title;
                }
                confirmModalBody.textContent = message;
                const onConfirm = () => {
                    resolve(true);
                    confirmModalConfirm.removeEventListener('click', onConfirm);
                    confirmModalEl.removeEventListener('hidden.bs.modal', onCancel);
                    confirmModal.hide();
                };
                const onCancel = () => {
                    resolve(false);
                    confirmModalConfirm.removeEventListener('click', onConfirm);
                    confirmModalEl.removeEventListener('hidden.bs.modal', onCancel);
                };
                confirmModalConfirm.addEventListener('click', onConfirm);
                confirmModalEl.addEventListener('hidden.bs.modal', onCancel, { once: true });
                confirmModal.show();
            });
        }

        function resetUserImportModal() {
            if (userImportMessage) {
                setMessage(userImportMessage);
            }
            if (userImportFileInput) {
                userImportFileInput.value = '';
            }
        }

        function resetAssignImportModal() {
            if (assignImportMessage) {
                setMessage(assignImportMessage);
            }
            if (assignImportFileInput) {
                assignImportFileInput.value = '';
            }
        }

        function clearDanglingBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.paddingRight = '';
        }

        function getDefaultEntryYear() {
            return String(new Date().getFullYear());
        }

        function getDefaultEntryTerm() {
            const month = new Date().getMonth() + 1;
            if (month >= 6 && month <= 8) return '02';
            if (month >= 9) return '03';
            return '01';
        }

        function toggleStudentEntryFields(role) {
            const isStudent = role === 'student';
            if (newEntryWrap) {
                newEntryWrap.hidden = !isStudent;
            }
            if (newEntryYearInput) {
                newEntryYearInput.disabled = !isStudent;
                if (isStudent && !newEntryYearInput.value) {
                    newEntryYearInput.value = getDefaultEntryYear();
                }
            }
            if (newEntryTermSelect) {
                newEntryTermSelect.disabled = !isStudent;
                if (isStudent && !newEntryTermSelect.value) {
                    newEntryTermSelect.value = getDefaultEntryTerm();
                }
            }
        }

        function downloadUserTemplate() {
            const content = [
                ['username', 'display_name', 'password', 'role', 'entry_year', 'entry_term'],
                ['student01', '学生01', 'pass1234', 'student', '2026', '01'],
                ['teacher01', '老师01', 'teachpass', 'teacher', '', ''],
                ['admin01', '管理员01', 'adminpass', 'admin', '', '']
            ].map((row) => row.join(',')).join('\n');
            const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'user_import_template.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function downloadAssignTemplate() {
            const content = [
                ['username', 'course_id'],
                ['student01', '1'],
                ['student02', '2']
            ].map((row) => row.join(',')).join('\n');
            const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'course_assignments_template.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        async function importUsers() {
            if (!userImportFileInput) return;
            const file = userImportFileInput.files && userImportFileInput.files[0];
            if (!file) {
                setMessage(userImportMessage, '请选择要导入的 CSV 文件', 'error');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                setMessage(userImportMessage, '文件过大，请控制在 5MB 内', 'error');
                return;
            }
            const formData = new FormData();
            formData.append('file', file);
            const originalLabel = userImportButton ? userImportButton.textContent : '';
            if (userImportButton) {
                userImportButton.disabled = true;
                userImportButton.textContent = '导入中...';
            }
            setMessage(userImportMessage, '正在导入，请稍候...');
            try {
                const result = await fetchJSON(`${API_BASE}/users_import.php`, {
                    method: 'POST',
                    body: formData
                });
                const inserted = Number(result.inserted ?? result.inserted_count ?? 0);
                const skipped = Number(result.skipped ?? result.skipped_count ?? 0);
                const errors = Array.isArray(result.errors) ? result.errors : [];
                const parts = [
                    `导入成功：${inserted} 条`,
                    `跳过：${skipped} 条`
                ];
                if (errors.length) {
                    parts.push(`错误 ${errors.length} 条：${errors.map((e) => (e && e.message) || e).join('；')}`);
                }
                setMessage(userImportMessage, parts.join('；'), errors.length ? 'error' : 'success');
                userImportFileInput.value = '';

                const usersData = await fetchJSON(`${API_BASE}/users.php`);
                state.users = (usersData.users || []).map((user) => ({
                    ...user,
                    id: Number(user.id)
                }));
                state.users.sort((a, b) => a.id - b.id);
                const preferredUserId = state.selectedUserId && state.users.find((u) => u.id === state.selectedUserId)
                    ? state.selectedUserId
                    : (state.users[0] ? state.users[0].id : null);
                state.selectedUserId = preferredUserId;
                refreshUserList();
                populateSelect(
                    assignUserSelect,
                    state.users,
                    'id',
                    (user) => (user.display_name ? `${user.display_name}（${user.username}）` : user.username),
                    preferredUserId || ''
                );
                selectUser(preferredUserId);
            } catch (error) {
                setMessage(userImportMessage, error.message || '导入失败', 'error');
            } finally {
                if (userImportButton) {
                    userImportButton.disabled = false;
                    userImportButton.textContent = originalLabel || '导入用户';
                }
            }
        }

        async function importAssignments() {
            if (!assignImportFileInput) return;
            const file = assignImportFileInput.files && assignImportFileInput.files[0];
            if (!file) {
                setMessage(assignImportMessage, '请选择要导入的 CSV 文件', 'error');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                setMessage(assignImportMessage, '文件过大，请控制在 5MB 内', 'error');
                return;
            }
            const formData = new FormData();
            formData.append('file', file);
            const originalLabel = assignImportButton ? assignImportButton.textContent : '';
            if (assignImportButton) {
                assignImportButton.disabled = true;
                assignImportButton.textContent = '导入中...';
            }
            setMessage(assignImportMessage, '正在导入，请稍候...');
            try {
                const result = await fetchJSON(`${API_BASE}/course_assignments_import.php`, {
                    method: 'POST',
                    body: formData
                });
                const inserted = Number(result.inserted ?? result.inserted_count ?? 0);
                const skipped = Number(result.skipped ?? result.skipped_count ?? 0);
                const errors = Array.isArray(result.errors) ? result.errors : [];
                const parts = [
                    `分配成功：${inserted} 条`,
                    `跳过：${skipped} 条`
                ];
                if (errors.length) {
                    parts.push(`错误 ${errors.length} 条：${errors.map((e) => (e && e.message) || e).join('；')}`);
                }
                setMessage(assignImportMessage, parts.join('；'), errors.length ? 'error' : 'success');
                assignImportFileInput.value = '';

                const selectedUserId = parseInt(assignUserSelect.value, 10);
                if (selectedUserId) {
                    await loadAssignmentsForUser(selectedUserId);
                }
            } catch (error) {
                setMessage(assignImportMessage, error.message || '导入失败', 'error');
            } finally {
                if (assignImportButton) {
                    assignImportButton.disabled = false;
                    assignImportButton.textContent = originalLabel || '导入分配';
                }
            }
        }

        async function fetchJSON(url, options = {}) {
            const response = await fetch(normalizeApiUrl(url), {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    ...options.headers
                },
                ...options
            });
            const data = await response.json().catch(() => null);
            if (!response.ok) {
                const message = (data && (data.message || data.error)) || '请求失败';
                throw new Error(message);
            }
            return data;
        }

        async function fetchCloudFiles() {
            setMessage(cloudPickerMessage);
            cloudPickerBody.innerHTML = '<tr><td colspan="4" class="text-secondary text-center py-4">正在加载云盘文件...</td></tr>';
            try {
                const data = await fetchJSON(FILES_ENDPOINT);
                cloudFiles = data.files || [];
                renderCloudPicker();
            } catch (error) {
                setMessage(cloudPickerMessage, error.message || '无法加载云盘文件', 'error');
                cloudPickerBody.innerHTML = '<tr><td colspan="4" class="text-secondary text-center py-4">云盘文件加载失败</td></tr>';
            }
        }

        function renderCloudPicker() {
            if (!cloudPickerBody) return;
            cloudPickerBody.innerHTML = '';
            if (!cloudFiles.length) {
                cloudPickerBody.innerHTML = '<tr><td colspan="4" class="text-secondary text-center py-4">云盘暂无文件，请先上传。</td></tr>';
                return;
            }
            cloudFiles.forEach((file) => {
                const tr = document.createElement('tr');
                const status = file.is_public ? '<span class="badge bg-success-subtle text-success">已开启</span>' : '<span class="badge bg-secondary">关闭</span>';
                tr.innerHTML = `
                <td>
                    <div class="fw-semibold">${file.original_name}</div>
                    <div class="text-secondary small">${file.mime_type || '未知类型'} · ${file.created_at}</div>
                </td>
                <td>${formatSize(file.size_bytes)}</td>
                <td>${status}</td>
                <td class="text-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <button class="nav-btn nav-btn-outline nav-btn-success py-1 px-2 small" data-cloud-insert="public" data-file-id="${file.id}">插入外链</button>
                    </div>
                </td>
            `;
                const publicBtn = tr.querySelector('[data-cloud-insert="public"]');
                publicBtn.addEventListener('click', () => insertFromCloud(file.id, 'public'));
                cloudPickerBody.appendChild(tr);
            });
        }

        async function ensurePublic(file) {
            if (file.is_public) {
                return file;
            }
            const data = await fetchJSON(FILES_ENDPOINT, {
                method: 'POST', // 兼容防火墙拦截 PATCH
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: file.id, is_public: true, _method: 'PATCH' })
            });
            const updated = data.file || file;
            cloudFiles = cloudFiles.map((item) => (item.id === updated.id ? updated : item));
            return updated;
        }

        async function insertFromCloud(fileId, mode = 'public') {
            if (!activeCloudTargetInputId) {
                setMessage(cloudPickerMessage, '未找到目标输入框', 'error');
                return;
            }
            const targetInput = document.getElementById(activeCloudTargetInputId);
            if (!targetInput) {
                setMessage(cloudPickerMessage, '输入框不存在或未渲染', 'error');
                return;
            }
            const file = cloudFiles.find((item) => Number(item.id) === Number(fileId));
            if (!file) {
                setMessage(cloudPickerMessage, '文件不存在', 'error');
                return;
            }
            let finalFile = file;
            try {
                setMessage(cloudPickerMessage, '正在开启外链...', 'success');
                finalFile = await ensurePublic(file);
            } catch (error) {
                setMessage(cloudPickerMessage, error.message || '外链开启失败', 'error');
                return;
            }
            const value = buildAbsoluteWithCurrentOrigin(finalFile.share_url);
            if (activeCloudTargetMode === 'attachment' || targetInput.tagName === 'TEXTAREA') {
                const line = `${finalFile.original_name}|${value}`;
                const prev = (targetInput.value || '').trim();
                targetInput.value = prev ? `${prev}\n${line}` : line;
            } else {
                targetInput.value = value;
            }
            setMessage(cloudPickerMessage, `已插入外链：${finalFile.original_name}`, 'success');
            if (cloudPickerModal) {
                cloudPickerModal.hide();
            }
        }

        async function openCloudPicker(targetInputId, mode = 'default') {
            activeCloudTargetInputId = targetInputId;
            activeCloudTargetMode = mode || 'default';
            clearDanglingBackdrops();
            if (!cloudPickerModal) {
                cloudPickerModal = new bootstrap.Modal(cloudPickerModalEl);
            }
            if (cloudPickerModalEl) {
                cloudPickerModalEl.addEventListener('hidden.bs.modal', clearDanglingBackdrops, { once: true });
            }
            await fetchCloudFiles();
            cloudPickerModal.show();
        }

        function uploadToCloud(files) {
            const payloads = Array.from(files || []);
            if (!payloads.length) {
                setMessage(cloudUploadMessage, '请选择文件', 'error');
                return Promise.resolve();
            }
            setMessage(cloudUploadMessage, `正在上传 ${payloads.length} 个文件...`);
            cloudUploadButton.disabled = true;
            if (cloudUploadProgressWrap) {
                cloudUploadProgressWrap.hidden = false;
                cloudUploadProgressBar.style.width = '0%';
                cloudUploadProgressBar.setAttribute('aria-valuenow', '0');
                cloudUploadProgressText.textContent = '';
            }
            const uploadSingle = (file, index) => new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('file', file);
                const xhr = new XMLHttpRequest();
                xhr.open('POST', normalizeApiUrl(FILES_ENDPOINT), true);
                xhr.withCredentials = true;
                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const pct = Math.round((e.loaded / e.total) * 100);
                        cloudUploadProgressBar.style.width = `${pct}%`;
                        cloudUploadProgressBar.setAttribute('aria-valuenow', String(pct));
                        cloudUploadProgressText.textContent = `文件 ${index + 1}/${payloads.length} · ${pct}%`;
                    }
                };
                xhr.onerror = () => reject(new Error('上传失败，请检查网络'));
                xhr.onload = () => {
                    let json = null;
                    try {
                        json = JSON.parse(xhr.responseText || '{}');
                    } catch (e) { /* ignore */ }
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve(json);
                    } else {
                        const message = (json && (json.message || json.error)) || `上传失败（${xhr.status}）`;
                        reject(new Error(message));
                    }
                };
                xhr.send(formData);
            });

            return payloads.reduce((promise, file, idx) => promise.then(async () => {
                await uploadSingle(file, idx);
            }), Promise.resolve()).then(() => {
                setMessage(cloudUploadMessage, '上传成功，已自动开启外链', 'success');
                cloudUploadInput.value = '';
            }).catch((error) => {
                setMessage(cloudUploadMessage, error.message || '上传失败', 'error');
            }).finally(async () => {
                cloudUploadButton.disabled = false;
                if (cloudUploadProgressWrap) {
                    cloudUploadProgressWrap.hidden = true;
                }
                await fetchCloudFiles();
                // 自动开启外链
                await Promise.all(cloudFiles.map(async (file) => {
                    if (!file.is_public) {
                        try {
                            await ensurePublic(file);
                        } catch (e) {
                            // ignore
                        }
                    }
                }));
                await fetchCloudFiles();
            });
        }

        function refreshUserList() {
            if (!userListEl) return;
            userListEl.innerHTML = '';
            if (!state.users.length) {
                const empty = document.createElement('li');
                empty.textContent = '暂无用户';
                empty.className = 'text-muted';
                userListEl.appendChild(empty);
                return;
            }
            state.users.forEach((user) => {
                const item = document.createElement('li');
                item.dataset.userId = user.id;
                item.setAttribute('role', 'button');
                item.tabIndex = 0;
                item.classList.add('selectable');
                if (state.selectedUserId === user.id) {
                    item.classList.add('active');
                }
                const info = document.createElement('div');
                info.className = 'user-meta';
                const nameEl = document.createElement('strong');
                nameEl.textContent = user.display_name || user.username;
                info.appendChild(nameEl);
                const metaEl = document.createElement('span');
                const metaParts = [];
                if (user.student_no) {
                    metaParts.push(`学号：${user.student_no}`);
                }
                metaParts.push(`用户名：${user.username}`);
                metaEl.textContent = metaParts.join(' · ');
                info.appendChild(metaEl);
                item.appendChild(info);
                const roleTag = document.createElement('span');
                let roleLabel = '学员';
                if (user.role === 'admin') roleLabel = '管理员';
                if (user.role === 'teacher') roleLabel = '老师';
                roleTag.className = 'user-role-tag' + (user.role === 'admin' ? ' is-admin' : '');
                roleTag.textContent = roleLabel;
                item.appendChild(roleTag);
                userListEl.appendChild(item);
            });
        }

        function selectUser(userId) {
            const numericId = typeof userId === 'number' ? userId : parseInt(userId, 10);
            const target = state.users.find((user) => user.id === numericId) || null;
            state.selectedUserId = target ? target.id : null;

            document.querySelectorAll('#userList li[data-user-id]').forEach((el) => {
                const matchId = parseInt(el.dataset.userId, 10);
                el.classList.toggle('active', !Number.isNaN(matchId) && matchId === state.selectedUserId);
            });

            if (!target) {
                if (assignUserSelect && assignUserSelect.value !== '') {
                    assignUserSelect.value = '';
                }
                userDetailTitle.textContent = '用户详情';
                userDetailSubtitle.textContent = state.users.length ? '请选择左侧的用户进行管理。' : '暂无用户，请先创建。';
                userDetailRoleChip.hidden = true;
                userDetailEmpty.hidden = false;
                updateUserForm.hidden = true;
                userDangerZone.hidden = true;
                if (resetPasswordButton) {
                    resetPasswordButton.disabled = true;
                }
                if (deleteUserButton) {
                    deleteUserButton.disabled = true;
                }
                editUsernameInput.value = '';
                editDisplayNameInput.value = '';
                editRoleSelect.value = 'student';
                if (editStudentNoInput) {
                    editStudentNoInput.value = '';
                }
                editPasswordInput.value = '';
                if (editEntryYearInput) {
                    editEntryYearInput.value = '';
                }
                if (editEntryTermSelect) {
                    editEntryTermSelect.value = getDefaultEntryTerm();
                }
                if (editEntryWrap) {
                    editEntryWrap.hidden = true;
                }
                setMessage(updateUserMessage);
                setMessage(deleteUserMessage);
                renderAssignmentPlaceholder(state.users.length ? '请选择用户查看已分配课程' : '暂无用户，请先创建。');
                return;
            }

            userDetailTitle.textContent = target.display_name || target.username;
            if (target.student_no) {
                userDetailSubtitle.textContent = `学号：${target.student_no} · 用户名：${target.username}`;
            } else {
                userDetailSubtitle.textContent = `用户名：${target.username}`;
            }
            userDetailRoleChip.hidden = false;
            let roleLabel = '学员';
            if (target.role === 'admin') roleLabel = '管理员';
            if (target.role === 'teacher') roleLabel = '老师';
            userDetailRoleChip.textContent = roleLabel;
            userDetailRoleChip.classList.toggle('is-admin', target.role === 'admin');
            userDetailEmpty.hidden = true;
            updateUserForm.hidden = false;
            userDangerZone.hidden = false;
            editUsernameInput.value = target.username;
            editDisplayNameInput.value = target.display_name || '';
            if (editStudentNoInput) {
                editStudentNoInput.value = target.student_no || '';
            }
            editRoleSelect.value = target.role;
            editPasswordInput.value = '';
            const isStudent = target.role === 'student';
            const hasStudentNo = Boolean(target.student_no);
            if (editEntryWrap) {
                editEntryWrap.hidden = !isStudent || hasStudentNo;
            }
            if (editEntryYearInput) {
                editEntryYearInput.value = '';
            }
            if (editEntryTermSelect) {
                editEntryTermSelect.value = getDefaultEntryTerm();
            }
            if (resetPasswordButton) {
                resetPasswordButton.disabled = false;
            }
            const isCurrentAdmin = state.currentUser && Number(state.currentUser.id) === target.id;
            if (deleteUserButton) {
                deleteUserButton.disabled = !!isCurrentAdmin;
                deleteUserButton.title = isCurrentAdmin ? '无法删除当前登录的管理员' : '';
            }
            setMessage(updateUserMessage);
            setMessage(deleteUserMessage);
            setMessage(assignCourseMessage);

            if (assignUserSelect) {
                const valueString = String(target.id);
                if (assignUserSelect.value !== valueString) {
                    assignUserSelect.value = valueString;
                }
            }

            loadAssignmentsForUser(target.id);
        }

        function refreshCourseList() {
            if (!courseListEl) return;
            courseListEl.innerHTML = '';
            setMessage(courseListMessage);
            if (!state.courses.length) {
                const empty = document.createElement('li');
                empty.textContent = '暂无课程';
                empty.style.color = 'var(--text-secondary)';
                courseListEl.appendChild(empty);
                return;
            }
            state.courses.forEach((course) => {
                const item = document.createElement('li');
                item.dataset.courseId = course.id;
                item.classList.add('selectable');
                item.setAttribute('role', 'button');
                item.tabIndex = 0;
                if (state.selectedCourseId === course.id) {
                    item.classList.add('active');
                }

                const info = document.createElement('div');
                info.style.flex = '1';
                const title = document.createElement('strong');
                title.textContent = course.title;
                info.appendChild(title);
                const meta = document.createElement('div');
                meta.className = 'text-muted';
                meta.style.fontSize = '0.85rem';
                const description = summarize(course.description || '', 64);
                const tagText = course.tags ? `标签：${course.tags}` : '无标签';
                const instructorText = course.instructor ? `讲师：${course.instructor}` : '讲师未填写';
                meta.textContent = description
                    ? `课程ID：${course.id} · ${description} · ${instructorText} · ${tagText}`
                    : `课程ID：${course.id} · ${instructorText} · ${tagText}`;
                info.appendChild(meta);
                item.appendChild(info);

                const actions = document.createElement('div');
                actions.className = 'list-actions';
                actions.style.display = 'flex';
                actions.style.gap = '0.5rem';
                actions.style.alignItems = 'center';

                const editButton = document.createElement('button');
                editButton.type = 'button';
                editButton.className = 'nav-btn nav-btn-outline py-1 px-2 small';
                editButton.dataset.courseId = course.id;
                editButton.dataset.courseAction = 'edit';
                editButton.innerHTML = '<i class="bi bi-pencil"></i> 编辑';
                actions.appendChild(editButton);

                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'nav-btn nav-btn-outline text-danger border-danger-subtle py-1 px-2 small';
                deleteButton.dataset.courseId = course.id;
                deleteButton.dataset.courseAction = 'delete';
                deleteButton.innerHTML = '<i class="bi bi-trash"></i> 删除';
                actions.appendChild(deleteButton);

                item.appendChild(actions);

                courseListEl.appendChild(item);
            });
        }

        function populateSelect(selectEl, items, valueKey, labelResolver, preferredValue = null) {
            if (!selectEl) return '';
            const fallbackValue = preferredValue ?? selectEl.value ?? '';
            selectEl.innerHTML = '';
            if (!items.length) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = '暂无数据';
                selectEl.appendChild(option);
                selectEl.disabled = true;
                return '';
            }
            selectEl.disabled = false;
            const fallbackString = fallbackValue !== null && fallbackValue !== undefined ? String(fallbackValue) : '';
            let matched = false;
            items.forEach((item) => {
                const option = document.createElement('option');
                const value = item[valueKey];
                option.value = value;
                const label = typeof labelResolver === 'function' ? labelResolver(item) : (item[labelResolver] || `ID ${value}`);
                option.textContent = label;
                if (!matched && String(value) === fallbackString) {
                    option.selected = true;
                    matched = true;
                }
                selectEl.appendChild(option);
            });
            if (!matched) {
                selectEl.selectedIndex = 0;
            }
            return selectEl.value;
        }

        function clearCourseEditor() {
            state.selectedCourseId = null;
            refreshCourseList();
        }

        function clearLessonEditor() {
            state.selectedLessonId = null;
            state.editingLessonOriginalCourseId = null;
            // No form to reset, modal handles itself
        }

        function summarize(text, maxLength = 60) {
            if (!text) {
                return '';
            }
            const clean = String(text).replace(/\s+/g, ' ').trim();
            if (clean.length <= maxLength) {
                return clean;
            }
            return `${clean.slice(0, maxLength)}…`;
        }

        function generateTemporaryPassword(length = 10) {
            const pool = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
            let result = '';
            for (let index = 0; index < length; index += 1) {
                const random = Math.floor(Math.random() * pool.length);
                result += pool.charAt(random);
            }
            return result;
        }

        function renderLessonPlaceholder(text, tone = 'muted') {
            if (!lessonListEl) return;
            lessonListEl.innerHTML = '';
            const item = document.createElement('li');
            item.textContent = text;
            if (tone === 'error') {
                item.style.color = '#b91c1c';
            } else {
                item.className = 'text-muted';
            }
            lessonListEl.appendChild(item);
        }

        function renderLessons(courseId, lessons) {
            if (!lessonListEl) return;
            lessonListEl.innerHTML = '';
            if (!Array.isArray(lessons) || !lessons.length) {
                renderLessonPlaceholder('该课程还没有课节');
                return;
            }
            if (state.editingLessonOriginalCourseId === courseId) {
                const exists = lessons.some((lesson) => lesson.id === state.selectedLessonId);
                if (!exists) {
                    clearLessonEditor();
                }
            }
            lessons.forEach((lesson, index) => {
                const item = document.createElement('li');
                item.dataset.lessonId = lesson.id;
                item.dataset.courseId = courseId;
                item.classList.add('selectable');
                item.setAttribute('role', 'button');
                item.tabIndex = 0;
                if (state.selectedLessonId === lesson.id) {
                    item.classList.add('active');
                }

                const info = document.createElement('div');
                info.style.flex = '1';
                const title = document.createElement('strong');
                title.textContent = `${index + 1}. ${lesson.title}`;
                info.appendChild(title);
                const meta = document.createElement('div');
                meta.className = 'text-muted';
                meta.style.fontSize = '0.85rem';
                const videoText = summarize(lesson.video_url || '', 70);
                const attachmentCount = Array.isArray(lesson.attachments) ? lesson.attachments.length : 0;
                const attachText = attachmentCount > 0 ? ` · ${attachmentCount} 个附件` : '';
                meta.textContent = videoText ? `课节ID：${lesson.id} · ${videoText}${attachText}` : `课节ID：${lesson.id}${attachText}`;
                info.appendChild(meta);
                item.appendChild(info);

                const actions = document.createElement('div');
                actions.className = 'list-actions';
                actions.style.display = 'flex';
                actions.style.gap = '0.5rem';
                actions.style.alignItems = 'center';

                const editButton = document.createElement('button');
                editButton.type = 'button';
                editButton.className = 'nav-btn nav-btn-outline py-1 px-2 small';
                editButton.dataset.lessonId = lesson.id;
                editButton.dataset.courseId = courseId;
                editButton.dataset.lessonAction = 'edit';
                editButton.innerHTML = '<i class="bi bi-pencil"></i> 编辑';
                actions.appendChild(editButton);

                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'nav-btn nav-btn-outline text-danger border-danger-subtle py-1 px-2 small';
                deleteButton.dataset.lessonId = lesson.id;
                deleteButton.dataset.courseId = courseId;
                deleteButton.dataset.lessonAction = 'delete';
                deleteButton.innerHTML = '<i class="bi bi-trash"></i> 删除';
                actions.appendChild(deleteButton);

                item.appendChild(actions);

                lessonListEl.appendChild(item);
            });
        }

        async function loadLessonsForCourse(courseId) {
            if (!courseId) {
                renderLessonPlaceholder('请选择课程查看课节');
                return;
            }
            renderLessonPlaceholder('正在加载课节...');
            try {
                const data = await fetchJSON(`${API_BASE}/courses.php?id=${courseId}`);
                const lessons = (data.lessons || []).map((lesson) => ({
                    ...lesson,
                    id: Number(lesson.id),
                    attachments: Array.isArray(lesson.attachments) ? lesson.attachments : (lesson.attachments ? lesson.attachments : [])
                }));
                state.lessons[courseId] = lessons;
                renderLessons(courseId, lessons);
                setMessage(lessonListMessage);
            } catch (error) {
                renderLessonPlaceholder(error.message || '加载课节失败', 'error');
                setMessage(lessonListMessage, error.message || '加载课节失败', 'error');
            }
        }

        function renderAssignmentPlaceholder(text, tone = 'muted') {
            if (!assignmentListEl) return;
            assignmentListEl.innerHTML = '';
            const item = document.createElement('li');
            item.textContent = text;
            if (tone === 'error') {
                item.style.color = '#b91c1c';
            } else {
                item.className = 'text-muted';
            }
            assignmentListEl.appendChild(item);
        }

        function renderAssignments(assignments) {
            if (!assignmentListEl) return;
            assignmentListEl.innerHTML = '';
            if (!assignments.length) {
                renderAssignmentPlaceholder('尚未分配课程');
                return;
            }
            assignments.forEach((assignment) => {
                const item = document.createElement('li');
                const info = document.createElement('div');
                const title = document.createElement('strong');
                title.textContent = assignment.course_title || `课程 ${assignment.course_id}`;
                info.appendChild(title);
                const meta = document.createElement('div');
                meta.className = 'text-muted';
                meta.style.fontSize = '0.85rem';
                const description = summarize(assignment.course_description || '', 64);
                meta.textContent = description ? `课程ID：${assignment.course_id} · ${description}` : `课程ID：${assignment.course_id}`;
                info.appendChild(meta);
                item.appendChild(info);

                const action = document.createElement('button');
                action.type = 'button';
                action.className = 'nav-btn nav-btn-outline text-danger border-danger-subtle py-1 px-2 small';
                action.dataset.courseId = assignment.course_id;
                action.innerHTML = '<i class="bi bi-trash"></i> 移除';
                item.appendChild(action);

                assignmentListEl.appendChild(item);
            });
        }

        async function loadAssignmentsForUser(userId) {
            if (!userId) {
                renderAssignmentPlaceholder('请选择用户查看已分配课程');
                return;
            }
            renderAssignmentPlaceholder('正在加载已分配课程...');
            try {
                const data = await fetchJSON(`${API_BASE}/course_assignments.php?user_id=${userId}`);
                renderAssignments(data.assignments || []);
            } catch (error) {
                renderAssignmentPlaceholder(error.message || '加载已分配课程失败', 'error');
            }
        }

        async function loadInitialData() {
            try {
                const session = await fetchJSON(`${API_BASE}/session.php`);
                if (!session.user || (session.user.role !== 'admin' && session.user.role !== 'teacher')) {
                    window.location.href = ROUTE_DASHBOARD;
                    return;
                }
                state.currentUser = session.user;
                if (adminChip) {
                    const roleLabel = session.user.role === 'teacher' ? '老师' : '管理员';
                    adminChip.textContent = `${session.user.display_name || session.user.username} · ${roleLabel}`;
                    adminChip.style.display = 'inline-flex';
                }
                let usersData = null;
                let coursesData = null;
                if (session.user.role === 'admin') {
                    [usersData, coursesData] = await Promise.all([
                        fetchJSON(`${API_BASE}/users.php`),
                        fetchJSON(`${API_BASE}/courses.php?all=1`)
                    ]);
                } else {
                    coursesData = await fetchJSON(`${API_BASE}/courses.php?all=1`);
                }

                if (session.user.role === 'admin') {
                    state.users = (usersData.users || []).map((user) => ({
                        ...user,
                        id: Number(user.id)
                    }));
                    state.users.sort((a, b) => a.id - b.id);
                    state.selectedUserId = state.users.length ? state.users[0].id : null;
                    refreshUserList();
                    const selectedUserOption = populateSelect(
                        assignUserSelect,
                        state.users,
                        'id',
                        (user) => (user.display_name ? `${user.display_name}（${user.username}）` : user.username),
                        state.selectedUserId
                    );
                    const normalizedUserId = parseInt(selectedUserOption, 10);
                    if (!Number.isNaN(normalizedUserId) && normalizedUserId > 0) {
                        selectUser(normalizedUserId);
                    } else {
                        selectUser(state.selectedUserId);
                    }
                } else {
                    state.users = [];
                    // hide user/assignment tabs
                    ['users', 'assignments', 'posts'].forEach((target) => {
                        const btn = document.querySelector(`.pill-tabs button[data-target="${target}"]`);
                        const tab = document.getElementById(`tab-${target}`);
                        if (btn) btn.style.display = 'none';
                        if (tab) tab.style.display = 'none';
                    });
                    const courseTabBtn = document.querySelector('.pill-tabs button[data-target="courses"]');
                    document.querySelectorAll('.pill-tabs button').forEach((btn) => btn.classList.remove('active'));
                    if (courseTabBtn) courseTabBtn.classList.add('active');
                    document.querySelectorAll('.tab-content').forEach((tab) => tab.classList.remove('active'));
                    const courseTab = document.getElementById('tab-courses');
                    if (courseTab) courseTab.classList.add('active');
                    renderAssignmentPlaceholder('教师无用户管理权限');
                }

                state.courses = coursesData.courses || [];
                refreshCourseList();
                const selectedLessonCourse = populateSelect(lessonCourseSelect, state.courses, 'id', 'title');
                populateSelect(editLessonCourseSelect, state.courses, 'id', 'title');

                if (session.user.role === 'admin') {
                    populateSelect(assignCourseSelect, state.courses, 'id', 'title');
                }

                const initialCourseId = parseInt(selectedLessonCourse, 10);
                if (initialCourseId) {
                    await loadLessonsForCourse(initialCourseId);
                } else if (!state.courses.length) {
                    renderLessonPlaceholder('暂无课程，请先创建。');
                } else {
                    renderLessonPlaceholder('请选择课程查看课节');
                }
            } catch (error) {
                alert(error.message || '加载管理信息失败');
                window.location.href = ROUTE_LOGIN;
            }
        }

        tabButtons.forEach((button) => {
            button.addEventListener('click', () => {
                tabButtons.forEach((btn) => btn.classList.toggle('active', btn === button));
                const target = button.dataset.target;
                tabContents.forEach((content) => {
                    content.classList.toggle('active', content.id === `tab-${target}`);
                });
            });
        });

        const urlParams = new URLSearchParams(window.location.search);
        const targetTab = urlParams.get('tab');
        if (targetTab) {
            const btn = document.querySelector(`.admin-tabs button[data-target="${targetTab}"]`);
            if (btn) {
                btn.click();
            }
        }

        if (assignUserSelect) {
            assignUserSelect.addEventListener('change', () => {
                const userId = parseInt(assignUserSelect.value, 10);
                if (Number.isNaN(userId) || userId <= 0) {
                    setMessage(assignCourseMessage);
                    selectUser(null);
                    return;
                }
                setMessage(assignCourseMessage);
                selectUser(userId);
            });
        }

        if (lessonCourseSelect) {
            lessonCourseSelect.addEventListener('change', () => {
                const courseId = parseInt(lessonCourseSelect.value, 10);
                setMessage(createLessonMessage);
                setMessage(lessonListMessage);
                if (state.editingLessonOriginalCourseId && state.editingLessonOriginalCourseId !== courseId) {
                    clearLessonEditor();
                }
                if (courseId) {
                    loadLessonsForCourse(courseId);
                } else if (!state.courses.length) {
                    renderLessonPlaceholder('暂无课程，请先创建。');
                } else {
                    renderLessonPlaceholder('请选择课程查看课节');
                }
            });
        }

        if (userListEl) {
            userListEl.addEventListener('click', (event) => {
                const item = event.target.closest('li[data-user-id]');
                if (!item) {
                    return;
                }
                const userId = parseInt(item.dataset.userId, 10);
                if (Number.isNaN(userId) || userId <= 0) {
                    return;
                }
                selectUser(userId);
            });
        }

        if (userListEl) {
            userListEl.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }
                const item = event.target.closest('li[data-user-id]');
                if (!item) {
                    return;
                }
                event.preventDefault();
                const userId = parseInt(item.dataset.userId, 10);
                if (Number.isNaN(userId) || userId <= 0) {
                    return;
                }
                selectUser(userId);
            });
        }

        courseListEl.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-course-id]');
            if (button) {
                const courseId = parseInt(button.dataset.courseId, 10);
                if (!courseId) {
                    return;
                }
                const action = button.dataset.courseAction || 'delete';
                if (action === 'edit') {
                    showCourseEditor(courseId);
                    return;
                }
                if (action !== 'delete') {
                    return;
                }
                const course = state.courses.find((item) => item.id === courseId);
                const courseLabel = course && course.title ? `课程「${course.title}」` : '该课程';
                const confirm = await showConfirm(`确定删除${courseLabel}？删除后将同步移除课节与课程分配。`);
                if (!confirm) {
                    return;
                }
                const originalLabel = button.textContent;
                button.disabled = true;
                button.textContent = '删除中...';
                setMessage(courseListMessage, '正在删除课程，请稍候...');
                try {
                    await fetchJSON(`${API_BASE}/courses.php`, {
                        method: 'POST', // 一些环境会拦截 DELETE，这里使用 POST + _method 兼容
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ course_id: courseId, _method: 'DELETE' })
                    });
                    const previousLessonSelection = lessonCourseSelect.value;
                    const previousAssignSelection = assignCourseSelect.value;
                    const removedActiveCourse = state.selectedCourseId === courseId;
                    const removedLessonEditingCourse = state.editingLessonOriginalCourseId === courseId;
                    state.courses = state.courses.filter((item) => item.id !== courseId);
                    delete state.lessons[courseId];
                    if (removedActiveCourse) {
                        clearCourseEditor();
                    } else {
                        refreshCourseList();
                    }
                    if (removedLessonEditingCourse) {
                        clearLessonEditor();
                    }
                    setMessage(courseListMessage, '课程已删除', 'success');

                    const nextLessonValue = populateSelect(
                        lessonCourseSelect,
                        state.courses,
                        'id',
                        'title',
                        previousLessonSelection && parseInt(previousLessonSelection, 10) !== courseId ? previousLessonSelection : ''
                    );

                    populateSelect(
                        assignCourseSelect,
                        state.courses,
                        'id',
                        'title',
                        previousAssignSelection && parseInt(previousAssignSelection, 10) !== courseId ? previousAssignSelection : ''
                    );

                    populateSelect(
                        editLessonCourseSelect,
                        state.courses,
                        'id',
                        'title',
                        state.editingLessonOriginalCourseId || ''
                    );

                    const selectedLessonCourseId = parseInt(nextLessonValue, 10);
                    if (selectedLessonCourseId) {
                        await loadLessonsForCourse(selectedLessonCourseId);
                    } else if (!state.courses.length) {
                        renderLessonPlaceholder('暂无课程，请先创建。');
                    } else {
                        renderLessonPlaceholder('请选择课程查看课节');
                    }
                    setMessage(lessonListMessage);

                    const selectedUserId = parseInt(assignUserSelect.value, 10);
                    if (selectedUserId) {
                        await loadAssignmentsForUser(selectedUserId);
                    } else {
                        renderAssignmentPlaceholder('请选择用户查看已分配课程');
                    }
                    setMessage(assignCourseMessage);
                    setMessage(createLessonMessage);
                } catch (error) {
                    setMessage(courseListMessage, error.message || '删除课程失败', 'error');
                    button.disabled = false;
                    button.textContent = originalLabel;
                }
                return;
            }

            const item = event.target.closest('li[data-course-id]');
            if (!item) {
                return;
            }
            const courseId = parseInt(item.dataset.courseId, 10);
            if (!courseId) {
                return;
            }
            showCourseEditor(courseId);
        });

        courseListEl.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }
            if (event.target instanceof HTMLElement && event.target.tagName === 'BUTTON') {
                return;
            }
            const item = event.target.closest('li[data-course-id]');
            if (!item) {
                return;
            }
            event.preventDefault();
            const courseId = parseInt(item.dataset.courseId, 10);
            if (!courseId) {
                return;
            }
            showCourseEditor(courseId);
        });

        if (assignmentListEl) {
            assignmentListEl.addEventListener('click', async (event) => {
                const button = event.target.closest('button[data-course-id]');
                if (!button) {
                    return;
                }
                const userId = parseInt(assignUserSelect.value, 10);
                const courseId = parseInt(button.dataset.courseId, 10);
                if (!userId || !courseId) {
                    return;
                }
                const originalLabel = button.textContent;
                button.disabled = true;
                button.textContent = '移除中...';
                try {
                    const formBody = new URLSearchParams({
                        action: 'delete',
                        user_id: String(userId),
                        course_id: String(courseId)
                    });
                    await fetchJSON(`${API_BASE}/course_assignments.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formBody.toString()
                    });
                    setMessage(assignCourseMessage, '已移除课程', 'success');
                    await loadAssignmentsForUser(userId);
                } catch (error) {
                    setMessage(assignCourseMessage, error.message || '移除课程失败', 'error');
                    button.disabled = false;
                    button.textContent = originalLabel;
                }
            });
        }

        lessonListEl.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-lesson-id]');
            if (button) {
                const lessonId = parseInt(button.dataset.lessonId, 10);
                const courseId = parseInt(button.dataset.courseId || lessonCourseSelect.value, 10);
                if (!lessonId) {
                    return;
                }
                const action = button.dataset.lessonAction || 'delete';
                if (action === 'edit') {
                    if (courseId) {
                        showLessonEditor(courseId, lessonId);
                    }
                    return;
                }
                if (action !== 'delete') {
                    return;
                }
                const confirm = await showConfirm('确定删除该课节？删除后无法恢复。');
                if (!confirm) {
                    return;
                }
                const originalLabel = button.textContent;
                button.disabled = true;
                button.textContent = '删除中...';
                try {
                    await fetchJSON(`${API_BASE}/lessons.php`, {
                        method: 'POST', // 兼容防火墙拦截 DELETE
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ lesson_id: lessonId, _method: 'DELETE' })
                    });
                    if (state.selectedLessonId === lessonId) {
                        clearLessonEditor();
                    }
                    setMessage(lessonListMessage, '课节已删除', 'success');
                    await loadLessonsForCourse(courseId);
                } catch (error) {
                    setMessage(lessonListMessage, error.message || '删除课节失败', 'error');
                    button.disabled = false;
                    button.textContent = originalLabel;
                }
                return;
            }

            const item = event.target.closest('li[data-lesson-id]');
            if (!item) {
                return;
            }
            const lessonId = parseInt(item.dataset.lessonId, 10);
            const courseId = parseInt(item.dataset.courseId || lessonCourseSelect.value, 10);
            if (!lessonId || !courseId) {
                return;
            }
            showLessonEditor(courseId, lessonId);
        });

        lessonListEl.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }
            if (event.target instanceof HTMLElement && event.target.tagName === 'BUTTON') {
                return;
            }
            const item = event.target.closest('li[data-lesson-id]');
            if (!item) {
                return;
            }
            event.preventDefault();
            const lessonId = parseInt(item.dataset.lessonId, 10);
            const courseId = parseInt(item.dataset.courseId || lessonCourseSelect.value, 10);
            if (!lessonId || !courseId) {
                return;
            }
            showLessonEditor(courseId, lessonId);
        });

        if (downloadUserTemplateButton) {
            downloadUserTemplateButton.addEventListener('click', downloadUserTemplate);
        }
        if (downloadAssignTemplateButton) {
            downloadAssignTemplateButton.addEventListener('click', downloadAssignTemplate);
        }
        if (userImportButton) {
            userImportButton.addEventListener('click', importUsers);
        }
        if (assignImportButton) {
            assignImportButton.addEventListener('click', importAssignments);
        }
        if (userImportModalEl && typeof bootstrap !== 'undefined') {
            userImportModal = new bootstrap.Modal(userImportModalEl);
            userImportModalEl.addEventListener('show.bs.modal', () => {
                resetUserImportModal();
            });
            userImportModalEl.addEventListener('hidden.bs.modal', clearDanglingBackdrops);
        }
        if (assignImportModalEl && typeof bootstrap !== 'undefined') {
            assignImportModal = new bootstrap.Modal(assignImportModalEl);
            assignImportModalEl.addEventListener('show.bs.modal', () => {
                resetAssignImportModal();
            });
            assignImportModalEl.addEventListener('hidden.bs.modal', clearDanglingBackdrops);
        }
        if (openUserImportModalButton) {
            openUserImportModalButton.addEventListener('click', (event) => {
                event.preventDefault();
                if (!userImportModal && userImportModalEl && typeof bootstrap !== 'undefined') {
                    userImportModal = new bootstrap.Modal(userImportModalEl);
                }
                if (userImportModal) {
                    clearDanglingBackdrops();
                    resetUserImportModal();
                    userImportModal.show();
                }
            });
        }

        // 公众号文章由服务端渲染，不在后台使用 fetch 请求
        if (openAssignImportModalButton) {
            openAssignImportModalButton.addEventListener('click', (event) => {
                event.preventDefault();
                if (!assignImportModal && assignImportModalEl && typeof bootstrap !== 'undefined') {
                    assignImportModal = new bootstrap.Modal(assignImportModalEl);
                }
                if (assignImportModal) {
                    clearDanglingBackdrops();
                    resetAssignImportModal();
                    assignImportModal.show();
                }
            });
        }

        if (createUserForm) {
            createUserForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const payload = {
                    username: document.getElementById('newUsername').value.trim(),
                    display_name: document.getElementById('newDisplayName').value.trim(),
                    password: document.getElementById('newPassword').value,
                    role: newRoleSelect ? newRoleSelect.value : document.getElementById('newRole').value
                };
                if (!payload.username || !payload.password) {
                    setMessage(createUserMessage, '用户名和密码不能为空', 'error');
                    return;
                }
                if (payload.role === 'student') {
                    const entryYear = newEntryYearInput ? newEntryYearInput.value.trim() : '';
                    const entryTerm = newEntryTermSelect ? newEntryTermSelect.value : '';
                    if (!entryYear || !entryTerm) {
                        setMessage(createUserMessage, '学员需要填写入库年份和期次', 'error');
                        return;
                    }
                    payload.entry_year = entryYear;
                    payload.entry_term = entryTerm;
                }
                setMessage(createUserMessage, '正在创建用户，请稍候...');
                try {
                    const result = await fetchJSON(`${API_BASE}/users.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const newUser = {
                        ...result.user,
                        id: Number(result.user.id)
                    };
                    state.users.push(newUser);
                    state.users.sort((a, b) => a.id - b.id);
                    state.selectedUserId = newUser.id;
                    refreshUserList();
                    populateSelect(
                        assignUserSelect,
                        state.users,
                        'id',
                        (user) => (user.display_name ? `${user.display_name}（${user.username}）` : user.username),
                        newUser.id
                    );
                    createUserForm.reset();
                    if (newRoleSelect) {
                        toggleStudentEntryFields(newRoleSelect.value);
                    }
                    if (newEntryTermSelect) {
                        newEntryTermSelect.value = getDefaultEntryTerm();
                    }
                    if (newEntryYearInput) {
                        newEntryYearInput.value = getDefaultEntryYear();
                    }
                    setMessage(createUserMessage, '创建成功', 'success');
                    setMessage(assignCourseMessage);
                    selectUser(newUser.id);
                } catch (error) {
                    setMessage(createUserMessage, error.message || '创建失败', 'error');
                }
            });

        }

        if (createCourseForm) {
            createCourseForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const payload = {
                    title: document.getElementById('courseTitleInput').value.trim(),
                    instructor: courseInstructorInput ? courseInstructorInput.value.trim() : '',
                    tags: courseTagsInput ? courseTagsInput.value.trim() : '',
                    description: document.getElementById('courseDescriptionInput').value.trim()
                };
                if (!payload.title) {
                    setMessage(createCourseMessage, '课程名称不能为空', 'error');
                    return;
                }
                setMessage(createCourseMessage, '正在创建课程，请稍候...');
                try {
                    const result = await fetchJSON(`${API_BASE}/courses.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const newCourse = {
                        ...result.course,
                        id: Number(result.course.id)
                    };
                    state.courses.push(newCourse);
                    state.courses.sort((a, b) => a.id - b.id);
                    refreshCourseList();
                    const newCourseId = newCourse.id;
                    populateSelect(assignCourseSelect, state.courses, 'id', 'title', newCourseId);
                    const lessonSelectValue = populateSelect(lessonCourseSelect, state.courses, 'id', 'title', newCourseId);
                    populateSelect(editLessonCourseSelect, state.courses, 'id', 'title', state.editingLessonOriginalCourseId || newCourseId);
                    createCourseForm.reset();
                    setMessage(createCourseMessage, '课程创建成功', 'success');
                    setMessage(lessonListMessage);
                    const createdCourseId = parseInt(lessonSelectValue, 10);
                    if (createdCourseId) {
                        loadLessonsForCourse(createdCourseId);
                    }
                } catch (error) {
                    setMessage(createCourseMessage, error.message || '创建失败', 'error');
                }
            });
        }

        if (updateCourseForm) {
            updateCourseForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (!state.selectedCourseId) {
                    setMessage(updateCourseMessage, '请选择需要编辑的课程', 'error');
                    return;
                }
                const payload = {
                    id: state.selectedCourseId,
                    title: editCourseTitleInput.value.trim(),
                    instructor: editCourseInstructorInput ? editCourseInstructorInput.value.trim() : '',
                    tags: editCourseTagsInput ? editCourseTagsInput.value.trim() : '',
                    description: editCourseDescriptionInput.value.trim()
                };
                if (!payload.title) {
                    setMessage(updateCourseMessage, '课程名称不能为空', 'error');
                    return;
                }
                setMessage(updateCourseMessage, '正在保存课程，请稍候...');
                try {
                    const result = await fetchJSON(`${API_BASE}/courses.php`, {
                        method: 'POST', // 兼容防火墙拦截 PATCH
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ ...payload, _method: 'PATCH' })
                    });
                    const updatedCourse = {
                        ...result.course,
                        id: Number(result.course.id)
                    };
                    const index = state.courses.findIndex((course) => course.id === updatedCourse.id);
                    if (index !== -1) {
                        state.courses[index] = updatedCourse;
                    } else {
                        state.courses.push(updatedCourse);
                    }
                    state.courses.sort((a, b) => a.id - b.id);
                    state.selectedCourseId = updatedCourse.id;
                    refreshCourseList();

                    const existingCourseIds = new Set(state.courses.map((course) => course.id));
                    const previousLessonSelection = parseInt(lessonCourseSelect.value, 10);
                    const previousAssignSelection = parseInt(assignCourseSelect.value, 10);
                    const previousEditLessonSelection = parseInt(editLessonCourseSelect.value, 10);

                    const resolvedLessonSelection = populateSelect(
                        lessonCourseSelect,
                        state.courses,
                        'id',
                        'title',
                        existingCourseIds.has(previousLessonSelection) ? previousLessonSelection : updatedCourse.id
                    );

                    populateSelect(
                        assignCourseSelect,
                        state.courses,
                        'id',
                        'title',
                        existingCourseIds.has(previousAssignSelection) ? previousAssignSelection : updatedCourse.id
                    );

                    const nextEditSelection = state.editingLessonOriginalCourseId && existingCourseIds.has(state.editingLessonOriginalCourseId)
                        ? state.editingLessonOriginalCourseId
                        : (existingCourseIds.has(previousEditLessonSelection) ? previousEditLessonSelection : updatedCourse.id);

                    populateSelect(
                        editLessonCourseSelect,
                        state.courses,
                        'id',
                        'title',
                        nextEditSelection
                    );

                    const selectedAssignmentsUserId = parseInt(assignUserSelect.value, 10);
                    if (selectedAssignmentsUserId) {
                        await loadAssignmentsForUser(selectedAssignmentsUserId);
                    }
                    setMessage(assignCourseMessage);

                    setMessage(updateCourseMessage, '课程信息已更新', 'success');
                    setMessage(courseListMessage, '课程信息已更新', 'success');

                    const activeLessonCourseId = parseInt(resolvedLessonSelection, 10);
                    if (activeLessonCourseId && Array.isArray(state.lessons[activeLessonCourseId])) {
                        renderLessons(activeLessonCourseId, state.lessons[activeLessonCourseId]);
                    }
                } catch (error) {
                    setMessage(updateCourseMessage, error.message || '保存失败', 'error');
                }
            });
        }


        if (assignCourseForm) {
            assignCourseForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const payload = {
                    user_id: parseInt(assignUserSelect.value, 10),
                    course_id: parseInt(assignCourseSelect.value, 10)
                };
                if (!payload.user_id || !payload.course_id) {
                    setMessage(assignCourseMessage, '请选择用户和课程', 'error');
                    return;
                }
                setMessage(assignCourseMessage, '正在分配，请稍候...');
                try {
                    await fetchJSON(`${API_BASE}/course_assignments.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    await loadAssignmentsForUser(payload.user_id);
                    setMessage(assignCourseMessage, '分配成功', 'success');
                } catch (error) {
                    setMessage(assignCourseMessage, error.message || '分配失败', 'error');
                }
            });
        }

        if (createPostForm && createPostMessage && createPostMessage.textContent) {
            createPostMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (createLessonForm) {
            createLessonForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const payload = {
                    course_id: parseInt(lessonCourseSelect.value, 10),
                    title: lessonTitleInput ? lessonTitleInput.value.trim() : '',
                    video_url: lessonVideoInput ? lessonVideoInput.value.trim() : '',
                    attachments: lessonAttachmentsInput ? lessonAttachmentsInput.value : '',
                    description: lessonDescriptionInput ? lessonDescriptionInput.value.trim() : ''
                };
                if (!payload.course_id || !payload.title) {
                    setMessage(createLessonMessage, '请选择课程并填写课节标题', 'error');
                    return;
                }
                setMessage(createLessonMessage, '正在添加课节...');
                try {
                    await fetchJSON(`${API_BASE}/lessons.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    createLessonForm.reset();
                    const selectedCourseAfterCreate = populateSelect(
                        lessonCourseSelect,
                        state.courses,
                        'id',
                        'title',
                        payload.course_id
                    );
                    if (lessonDescriptionInput) {
                        lessonDescriptionInput.value = '';
                    }
                    setMessage(createLessonMessage, '课节添加成功', 'success');
                    setMessage(lessonListMessage);
                    const refreshedCourseId = parseInt(selectedCourseAfterCreate, 10);
                    if (refreshedCourseId) {
                        loadLessonsForCourse(refreshedCourseId);
                    }
                } catch (error) {
                    setMessage(createLessonMessage, error.message || '添加失败', 'error');
                }
            });
        }

        if (updateLessonForm) {
            updateLessonForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (!state.selectedLessonId) {
                    setMessage(updateLessonMessage, '请选择需要编辑的课节', 'error');
                    return;
                }
                const payload = {
                    lesson_id: state.selectedLessonId,
                    course_id: parseInt(editLessonCourseSelect.value, 10),
                    title: editLessonTitleInput.value.trim(),
                    video_url: editLessonVideoInput.value.trim(),
                    attachments: editLessonAttachmentsInput ? editLessonAttachmentsInput.value : '',
                    description: editLessonDescriptionInput ? editLessonDescriptionInput.value.trim() : ''
                };
                if (!payload.course_id || !payload.title) {
                    setMessage(updateLessonMessage, '请选择课程并填写课节标题', 'error');
                    return;
                }
                setMessage(updateLessonMessage, '正在保存课节，请稍候...');
                try {
                    const result = await fetchJSON(`${API_BASE}/lessons.php`, {
                        method: 'POST', // 兼容防火墙拦截 PATCH
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ ...payload, _method: 'PATCH' })
                    });
                    const updatedLesson = {
                        ...result.lesson,
                        id: Number(result.lesson.id),
                        course_id: Number(result.lesson.course_id)
                    };
                    const previousCourseId = state.editingLessonOriginalCourseId;
                    state.selectedLessonId = updatedLesson.id;
                    state.editingLessonOriginalCourseId = updatedLesson.course_id;

                    populateSelect(editLessonCourseSelect, state.courses, 'id', 'title', updatedLesson.course_id);
                    if (lessonCourseSelect.value !== String(updatedLesson.course_id)) {
                        lessonCourseSelect.value = String(updatedLesson.course_id);
                    }

                    await loadLessonsForCourse(updatedLesson.course_id);
                    if (previousCourseId && previousCourseId !== updatedLesson.course_id) {
                        await loadLessonsForCourse(previousCourseId);
                    }

                    setMessage(updateLessonMessage, '课节已更新', 'success');
                    setMessage(lessonListMessage, '课节已更新', 'success');
                } catch (error) {
                    setMessage(updateLessonMessage, error.message || '保存失败', 'error');
                }
            });
        }


        if (editCourseModalEl && typeof bootstrap !== 'undefined') {
            editCourseModal = new bootstrap.Modal(editCourseModalEl);
        }
        if (editLessonModalEl && typeof bootstrap !== 'undefined') {
            editLessonModal = new bootstrap.Modal(editLessonModalEl);
        }
        if (editPostModalEl && typeof bootstrap !== 'undefined') {
            editPostModal = new bootstrap.Modal(editPostModalEl);
        }

        if (submitEditCourseModalBtn) {
            submitEditCourseModalBtn.addEventListener('click', async () => {
                if (!modalEditCourseId.value) return;
                const payload = {
                    id: modalEditCourseId.value,
                    title: modalEditCourseTitle.value.trim(),
                    instructor: modalEditCourseInstructor.value.trim(),
                    tags: modalEditCourseTags.value.trim(),
                    description: modalEditCourseDescription.value.trim(),
                    _method: 'PATCH'
                };
                if (!payload.title) {
                    setMessage(editCourseModalMessage, '课程名称不能为空', 'error');
                    return;
                }
                setMessage(editCourseModalMessage, '正在保存...', 'info');
                try {
                    const result = await fetchJSON(`${API_BASE}/courses.php`, {
                        method: 'POST', body: JSON.stringify(payload), headers: { 'Content-Type': 'application/json' }
                    });
                    const updatedCourse = {
                        ...result.course,
                        id: Number(result.course.id)
                    };
                    const index = state.courses.findIndex((course) => course.id === updatedCourse.id);
                    if (index !== -1) state.courses[index] = updatedCourse;
                    else state.courses.push(updatedCourse);
                    state.courses.sort((a, b) => a.id - b.id);
                    refreshCourseList();
                    // Update selects
                    populateSelect(lessonCourseSelect, state.courses, 'id', 'title');
                    populateSelect(assignCourseSelect, state.courses, 'id', 'title');

                    editCourseModal.hide();
                    setMessage(createCourseMessage, '课程已更新', 'success');
                } catch (e) {
                    setMessage(editCourseModalMessage, e.message, 'error');
                }
            });
        }

        if (submitEditLessonModalBtn) {
            submitEditLessonModalBtn.addEventListener('click', async () => {
                const lid = modalEditLessonId.value;
                const cid = modalEditLessonCourseSelect.value;
                if (!lid || !cid) return;
                const payload = {
                    lesson_id: lid,
                    course_id: cid,
                    title: modalEditLessonTitle.value.trim(),
                    video_url: modalEditLessonVideo.value.trim(),
                    attachments: modalEditLessonAttachments.value,
                    description: modalEditLessonDescription.value.trim(),
                    _method: 'PATCH'
                };
                if (!payload.title) {
                    setMessage(editLessonModalMessage, '标题不能为空', 'error');
                    return;
                }
                setMessage(editLessonModalMessage, '正在保存...', 'info');
                try {
                    const result = await fetchJSON(`${API_BASE}/lessons.php`, {
                        method: 'POST', body: JSON.stringify(payload), headers: { 'Content-Type': 'application/json' }
                    });
                    editLessonModal.hide();
                    // Refresh lessons for the course
                    loadLessonsForCourse(cid);
                    setMessage(lessonListMessage, '课节已更新', 'success');
                } catch (e) {
                    setMessage(editLessonModalMessage, e.message, 'error');
                }
            });
        }

        if (submitEditPostModalBtn && editPostFormModal) {
            submitEditPostModalBtn.addEventListener('click', () => {
                editPostFormModal.submit();
            });
        }

        // --- Wiring Edit Buttons Open Modal ---
        function showCourseEditor(courseId) {
            const course = state.courses.find(c => c.id === Number(courseId));
            if (!course) return;
            if (editCourseModal) {
                modalEditCourseId.value = course.id;
                modalEditCourseTitle.value = course.title || '';
                modalEditCourseInstructor.value = course.instructor || '';
                modalEditCourseTags.value = course.tags || '';
                modalEditCourseDescription.value = course.description || '';
                setMessage(editCourseModalMessage);
                editCourseModal.show();
            }
        }
        function showLessonEditor(courseId, lessonId) {
            const lessons = state.lessons[courseId] || [];
            const lesson = lessons.find(l => l.id === Number(lessonId));
            if (!lesson) return;

            if (editLessonModal) {
                modalEditLessonId.value = lesson.id;
                // populate course select
                populateSelect(modalEditLessonCourseSelect, state.courses, 'id', 'title', courseId);
                modalEditLessonTitle.value = lesson.title || '';
                modalEditLessonVideo.value = lesson.video_url || '';
                if (Array.isArray(lesson.attachments)) {
                    modalEditLessonAttachments.value = lesson.attachments.map(a => `${a.title || a.url}|${a.url}`).join('\n');
                } else {
                    modalEditLessonAttachments.value = lesson.attachments || '';
                }
                modalEditLessonDescription.value = lesson.description || '';
                setMessage(editLessonModalMessage);
                editLessonModal.show();
            }
        }

        if (postListEl) {
            postListEl.addEventListener('click', (e) => {
                const btn = e.target.closest('.edit-post-btn');
                if (btn) {
                    const post = JSON.parse(btn.dataset.post);
                    if (editPostModal) {
                        modalEditPostId.value = post.id;
                        modalEditPostTitle.value = post.title;
                        modalEditPostLink.value = post.link_url || '';
                        modalEditPostDate.value = post.published_at ? post.published_at.split(' ')[0] : '';
                        modalEditPostAuthor.value = post.author || '';
                        modalEditPostTags.value = post.tags || '';
                        modalEditPostSummary.value = post.summary || '';
                        editPostModal.show();
                    }
                }
            });
        }


        function clearDanglingBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }

        loadInitialData();

        // Clear up dangling backdrops occasionally
        clearDanglingBackdrops();
    </script>
</body>

</html>