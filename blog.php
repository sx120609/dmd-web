<?php
$blogPosts = [];
$configFile = __DIR__ . '/config.php';
// 保持原有 PHP 逻辑不变
if (file_exists($configFile)) {
    $config = require $configFile;
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
        $result = $mysqli->query('SELECT id, title, summary, link_url, published_at, author, tags, created_at FROM blog_posts ORDER BY COALESCE(published_at, created_at) DESC, id DESC');
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['id'] = (int) $row['id'];
                $blogPosts[] = $row;
            }
            $result->free();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>项目日志 · Rare Light</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            /* 核心色盘：更现代、更高级的蓝紫色调 */
            --rl-bg: #f8fafc;
            --rl-text-main: #0f172a;
            --rl-text-muted: #64748b;
            --rl-primary: #3b82f6;
            --rl-accent: #8b5cf6;
            
            /* 渐变色 */
            --gradient-brand: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            --gradient-glow: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.05), transparent 70%);
            
            /* 卡片样式 */
            --card-bg: rgba(255, 255, 255, 0.85);
            --card-border: 1px solid rgba(255, 255, 255, 0.6);
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            --card-shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Noto Sans SC', system-ui, sans-serif;
            background-color: var(--rl-bg);
            background-image: var(--gradient-glow);
            background-attachment: fixed;
            background-size: 100% 100vh;
            background-repeat: no-repeat;
            color: var(--rl-text-main);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* --- 导航栏优化 --- */
        .site-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
            padding: 0.75rem 0;
            transition: all 0.3s ease;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--rl-text-main);
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            background: var(--gradient-brand);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .brand-text h1 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 0.75rem;
            color: var(--rl-text-muted);
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .nav-btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .nav-btn-ghost {
            color: var(--rl-text-muted);
        }
        .nav-btn-ghost:hover {
            color: var(--rl-text-main);
            background: rgba(0,0,0,0.03);
        }

        .nav-btn-primary {
            background: var(--rl-text-main);
            color: white;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }
        .nav-btn-primary:hover {
            transform: translateY(-1px);
            background: #1e293b;
            color: white;
        }

        /* --- Hero 区域 --- */
        .blog-hero {
            padding: 5rem 0 3rem;
            text-align: center;
            position: relative;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--rl-primary);
            border-radius: 99px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 1.15rem;
            color: var(--rl-text-muted);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* --- 卡片网格 --- */
        .content-section {
            padding-bottom: 5rem;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.5rem;
        }

        .blog-card {
            background: var(--card-bg);
            border: var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .blog-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-shadow-hover);
            border-color: rgba(59, 130, 246, 0.3);
            background: #fff;
        }

        /* 卡片顶部的装饰条（可选） */
        .blog-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-brand);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .blog-card:hover::before {
            opacity: 1;
        }

        .card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--rl-text-muted);
        }

        .meta-date {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .meta-tag {
            background: #f1f5f9;
            color: #475569;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--rl-text-main);
            line-height: 1.4;
            margin: 0;
        }

        .card-summary {
            color: var(--rl-text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
            flex-grow: 1; /* 让摘要占据剩余空间，保证底部按钮对齐 */
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid rgba(0,0,0,0.04);
        }

        .read-more-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--rl-primary);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: gap 0.2s;
        }

        .read-more-btn:hover {
            gap: 12px;
            color: var(--rl-accent);
        }
        
        /* 空状态样式 */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255,255,255,0.5);
            border-radius: 16px;
            border: 2px dashed rgba(0,0,0,0.05);
            color: var(--rl-text-muted);
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
            .hero-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<nav class="site-nav">
    <div class="container-xxl d-flex align-items-center justify-content-between">
        <a href="/rarelight/" class="nav-brand">
            <div class="brand-logo">RL</div>
            <div class="brand-text">
                <span>PROJECT LOGS</span>
                <h1>Rare Light</h1>
            </div>
        </a>
        <div class="d-flex gap-2">
            <a class="nav-btn nav-btn-ghost d-none d-sm-inline-flex" href="/rarelight/">返回首页</a>
            <a class="nav-btn nav-btn-primary" href="/rarelight/dashboard">
                <i class="bi bi-grid-fill me-2"></i>进入课堂
            </a>
        </div>
    </div>
</nav>

<header class="blog-hero">
    <div class="container-xxl">
        <div class="hero-badge">
            <i class="bi bi-stars"></i> 项目动态更新
        </div>
        <h1 class="hero-title">项目日志与进展</h1>
        <p class="hero-desc">记录 Rare Light 项目的需求调研、开发进度与阶段性成果展示。这里的每一个脚印，都是通往最终产品的基石。</p>
    </div>
</header>

<main class="content-section">
    <div class="container-xxl">
        
        <div class="d-flex align-items-end justify-content-between mb-4">
            <h2 class="h4 fw-bold mb-0 text-dark">
                <i class="bi bi-journal-richtext me-2 text-primary"></i>最新文章
            </h2>
            <span class="text-secondary small">共 <?php echo count($blogPosts); ?> 篇记录</span>
        </div>

        <div class="grid-container">
            <?php if (empty($blogPosts)) : ?>
                <div class="empty-state">
                    <i class="bi bi-inbox fs-1 mb-3 d-block text-secondary opacity-50"></i>
                    <p class="mb-0">暂无项目日志，请在后台添加新的进展。</p>
                </div>
            <?php else : ?>
                <?php foreach ($blogPosts as $post) : ?>
                    <?php
                    $date = $post['published_at'] ?? $post['created_at'] ?? '';
                    $dateText = $date ? date('M d, Y', strtotime($date)) : 'Date Unknown';
                    $summary = trim((string) ($post['summary'] ?? ''));
                    if ($summary === '') {
                        $summary = '点击阅读详细内容与完整报告。';
                    }
                    $link = trim((string) ($post['link_url'] ?? ''));
                    $author = !empty($post['author']) ? $post['author'] : 'Team';
                    ?>
                    <article class="blog-card">
                        <div class="card-meta">
                            <div class="meta-date">
                                <i class="bi bi-calendar-event text-secondary" style="font-size: 0.8rem;"></i>
                                <span><?php echo htmlspecialchars($dateText, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <span class="meta-tag"><?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        
                        <h3 class="card-title">
                            <?php echo htmlspecialchars($post['title'] ?? '无标题日志', ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                        
                        <p class="card-summary">
                            <?php echo htmlspecialchars($summary, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        
                        <div class="card-footer">
                            <a href="<?php echo htmlspecialchars($link ?: '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="read-more-btn">
                                阅读完整文章 <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>