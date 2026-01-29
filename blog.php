<?php
$blogPosts = [];
$configFile = __DIR__ . '/config.php';
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
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>项目日志 · Rare Light</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        :root {
            --home-bg: radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.12), transparent 25%),
                radial-gradient(circle at 80% 10%, rgba(45, 212, 191, 0.14), transparent 26%),
                radial-gradient(circle at 80% 70%, rgba(76, 29, 149, 0.08), transparent 40%),
                #f8fafc;
            --card-shadow: 0 24px 80px rgba(15, 23, 42, 0.12);
            --card-border: 1px solid rgba(148, 163, 184, 0.14);
            --deep-gradient: linear-gradient(135deg, #2563eb, #60a5fa, #22d3ee);
            --pill-bg: rgba(255, 255, 255, 0.65);
        }

        body.blog {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--home-bg);
            color: #0f172a;
            min-height: 100vh;
        }

        .site-nav {
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(16px);
            background: rgba(248, 250, 252, 0.9);
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }

        .nav-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--deep-gradient);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.35);
        }

        .brand-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: var(--pill-bg);
            color: #1e293b;
            font-weight: 600;
            font-size: 0.78rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .nav-actions {
            display: flex;
            gap: 0.6rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .primary-button,
        .ghost-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.55rem 1.1rem;
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, 0.12);
            font-weight: 600;
            color: #0f172a;
            background: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: transform 160ms ease, box-shadow 160ms ease;
            min-height: 42px;
        }

        .primary-button {
            background: var(--deep-gradient);
            color: #fff;
            border: none;
        }

        .primary-button:hover,
        .ghost-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .blog-hero {
            padding: clamp(2.5rem, 6vw, 4rem) 0 2rem;
        }

        .blog-hero .hero-panel {
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.92);
            border: var(--card-border);
            color: #0f172a;
            box-shadow: var(--card-shadow);
        }

        .blog-hero .hero-pill {
            background: rgba(37, 99, 235, 0.1);
            color: #1d4ed8;
        }

        .hero-title {
            font-size: clamp(2.2rem, 4vw, 3.2rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.6rem;
            color: #0f172a;
            text-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
        }

        .hero-subtitle {
            color: #475569;
            line-height: 1.7;
        }

        .section-title {
            font-size: clamp(1.4rem, 2.6vw, 2rem);
            font-weight: 800;
            letter-spacing: -0.01em;
            margin: 0 0 0.5rem;
        }

        .section-subtitle {
            color: #64748b;
            margin-bottom: 1.2rem;
        }

        .wechat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .wechat-card {
            border-radius: 18px;
            border: var(--card-border);
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            padding: 1.2rem 1.3rem;
            display: grid;
            gap: 0.6rem;
        }

        .wechat-meta {
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .wechat-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #0f172a;
        }

        .wechat-summary {
            color: #475569;
            line-height: 1.6;
        }

        .wechat-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 600;
            color: #1d4ed8;
            text-decoration: none;
        }

        .blog-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 320px);
            gap: 1.5rem;
        }

        .sticky-side {
            position: sticky;
            top: 110px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .side-card {
            border-radius: 18px;
            border: var(--card-border);
            background: rgba(255, 255, 255, 0.92);
            padding: 1.25rem 1.3rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        .side-card h3 {
            font-size: 1.05rem;
            margin-bottom: 0.75rem;
        }

        @media (max-width: 992px) {
            .blog-layout {
                grid-template-columns: minmax(0, 1fr);
            }
            .sticky-side {
                position: static;
            }
            .nav-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body class="blog">
<nav class="site-nav">
    <div class="container-xxl py-3 d-flex align-items-center justify-content-between gap-3">
        <div class="nav-brand">
            <span class="brand-mark">RL</span>
            <div>
                <div class="small text-uppercase text-secondary">Rare Light</div>
                <div class="fw-bold">Rare Light · 项目日志</div>
            </div>
        </div>
        <div class="nav-actions">
            <a class="ghost-button" href="/rarelight/">返回首页</a>
            <a class="primary-button" href="/rarelight/dashboard">进入课堂</a>
        </div>
    </div>
</nav>

<section class="blog-hero">
    <div class="container-xxl hero-container">
        <div class="hero-panel p-4 p-lg-5">
            <div class="brand-eyebrow">Rare Light · 项目展示</div>
            <div class="hero-main">
                <div class="hero-copy">
                    <h1 class="hero-title mb-3">项目日志 / Blog</h1>
                    <p class="hero-subtitle mb-0">这里记录项目进展、需求调研与阶段成果。后续内容可持续补充。</p>
                </div>
                <div class="hero-meta">
                    <span class="hero-pill">可持续更新</span>
                    <span class="hero-pill soft">面向展示与汇报</span>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="container-xxl pb-5">
    <section class="mb-4">
        <h2 class="section-title">公众号文章精选</h2>
        <p class="section-subtitle">将公众号文章链接填到卡片里，作为对外展示内容。</p>
        <div class="wechat-grid" id="wechatList">
            <?php if (empty($blogPosts)) : ?>
                <div class="wechat-card text-secondary">暂无公众号文章，请先在后台新增。</div>
            <?php else : ?>
                <?php foreach ($blogPosts as $post) : ?>
                    <?php
                    $date = $post['published_at'] ?? $post['created_at'] ?? '';
                    $dateText = $date ? date('Y-m-d', strtotime($date)) : '';
                    $summary = trim((string) ($post['summary'] ?? ''));
                    if ($summary === '') {
                        $summary = '点击阅读公众号原文。';
                    }
                    $link = trim((string) ($post['link_url'] ?? ''));
                    ?>
                    <article class="wechat-card">
                        <div class="wechat-meta">
                            <?php if ($dateText) : ?><span><?php echo htmlspecialchars($dateText, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                            <span>公众号推文</span>
                            <?php if (!empty($post['author'])) : ?><span>负责人：<?php echo htmlspecialchars($post['author'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                        </div>
                        <div class="wechat-title"><?php echo htmlspecialchars($post['title'] ?? '未命名文章', ENT_QUOTES, 'UTF-8'); ?></div>
                        <p class="wechat-summary"><?php echo htmlspecialchars($summary, ENT_QUOTES, 'UTF-8'); ?></p>
                        <a class="wechat-link" href="<?php echo htmlspecialchars($link ?: '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                            阅读原文 →
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <aside class="sticky-side">
        <div class="side-card">
            <h3>如何新增内容</h3>
            <ol class="mb-0 text-secondary">
                <li>复制一张“公众号文章”卡片</li>
                <li>粘贴文章链接与标题/摘要</li>
                <li>保存并刷新页面</li>
            </ol>
        </div>
        <div class="side-card">
            <h3>展示建议</h3>
            <ul class="mb-0 text-secondary">
                <li>按时间顺序排列</li>
                <li>标题尽量具体</li>
                <li>摘要突出成果与影响</li>
            </ul>
        </div>
    </aside>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

