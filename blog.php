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
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&display=swap"
        rel="stylesheet">

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

            /* 原始Logo所需的渐变和阴影 */
            --deep-gradient: linear-gradient(135deg, #2563eb, #60a5fa, #22d3ee);

            /* 页面背景渐变 */
            --gradient-glow: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.05), transparent 70%);

            /* 卡片样式 */
            --card-bg: rgba(255, 255, 255, 0.85);
            --card-border: 1px solid rgba(255, 255, 255, 0.6);
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
            overflow-x: hidden;
            width: 100%;
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

        /* * === 关键修改：完全保留原始 Logo 样式 ===
         * 直接使用用户提供的 CSS，确保 100% 一致
         */
        .nav-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-family: 'Inter', sans-serif;
            /* 确保Logo区域使用原始字体 */
            text-decoration: none;
            /* 去除链接下划线 */
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
            font-size: 1.1rem;
            /* 微调字体大小以匹配原图 */
        }

        .brand-text .small {
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            /* text-secondary */
            font-weight: 600;
        }

        .brand-text .fw-bold {
            font-weight: 700 !important;
            color: #0f172a;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
        }

        /* === Logo 样式结束 === */


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
            background: rgba(0, 0, 0, 0.03);
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

        /* 工具条样式 (字号/语言) */
        .tool-bar {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 4px;
            border-radius: 10px;
            margin-right: 0.5rem;
        }

        .tool-btn {
            border: none;
            background: transparent;
            color: var(--rl-text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .tool-btn:hover {
            background: rgba(0, 0, 0, 0.05);
            color: var(--rl-text-main);
        }

        .tool-btn:active {
            transform: scale(0.95);
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

        /* 卡片顶部的装饰条 */
        .blog-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            /* 使用Logo同款渐变 */
            background: var(--deep-gradient);
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
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.04);
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
            background: rgba(255, 255, 255, 0.5);
            border-radius: 16px;
            border: 2px dashed rgba(0, 0, 0, 0.05);
            color: var(--rl-text-muted);
        }

        /* 移动端底部导航 */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 10px 16px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            justify-content: space-around;
            align-items: center;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: max(10px, env(safe-area-inset-bottom));
        }

        @media (max-width: 992px) {
            /* 只隐藏导航栏右侧的工具区，保留 Logo */
            .site-nav .d-flex.align-items-center:not(.container-xxl) {
                display: none !important;
            }

            .mobile-bottom-nav {
                display: flex;
            }

            body {
                padding-bottom: 80px;
            }
            
            /* 确保底部按钮文字居中 */
            .mobile-bottom-nav .nav-btn {
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
            }

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
                <span class="brand-mark">RL</span>
                <div class="brand-text">
                    <div class="small text-uppercase" data-i18n="brandTagline">RARE LIGHT</div>
                    <div class="fw-bold" data-i18n="brandTitle">Rare Light · 罕见病儿童公益</div>
                </div>
            </a>
            <div class="d-flex align-items-center">
                <div class="d-none d-lg-flex align-items-center">
                    <div class="tool-bar" aria-label="Font size controls">
                        <button type="button" class="tool-btn" id="fontSmaller" aria-label="Smaller font"><i
                                class="bi bi-type"></i>-</button>
                        <button type="button" class="tool-btn" id="fontReset" aria-label="Default font"><i
                                class="bi bi-arrow-counterclockwise"></i></button>
                        <button type="button" class="tool-btn" id="fontLarger" aria-label="Larger font"><i
                                class="bi bi-type"></i>+</button>
                    </div>

                    <button class="nav-btn nav-btn-ghost me-2" type="button" id="langToggle"
                        aria-label="Language toggle">
                        <i class="bi bi-translate me-1"></i> EN / 中文
                    </button>
                </div>

                <a class="nav-btn nav-btn-ghost d-none d-sm-inline-flex" href="/rarelight/" data-i18n="navHome">返回首页</a>
                <a class="nav-btn nav-btn-primary ms-2" href="/rarelight/dashboard">
                    <i class="bi bi-grid-fill me-2"></i><span data-i18n="navLogin">进入课堂</span>
                </a>
            </div>
        </div>
    </nav>

    <header class="blog-hero">
        <div class="container-xxl">
            <div class="hero-badge" data-i18n="heroBadge">
                <i class="bi bi-stars"></i> 公益项目动态
            </div>
            <h1 class="hero-title" data-i18n="heroTitle">项目日志与进展</h1>
            <p class="hero-desc" data-i18n="heroDesc">记录 Rare Light 在罕见病儿童公益领域的行动点滴。这里的每一个脚印，都凝聚着爱与希望。</p>
        </div>
    </header>

    <main class="content-section">
        <div class="container-xxl">

            <div class="d-flex align-items-end justify-content-between mb-4">
                <h2 class="h4 fw-bold mb-0 text-dark">
                    <i class="bi bi-journal-richtext me-2 text-primary"></i><span data-i18n="latestArticles">最新文章</span>
                </h2>
                <span class="text-secondary small"><span data-i18n="totalRecordsPrefix">共</span>
                    <?php echo count($blogPosts); ?> <span data-i18n="totalRecordsSuffix">篇记录</span></span>
            </div>

            <div class="grid-container">
                <?php if (empty($blogPosts)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox fs-1 mb-3 d-block text-secondary opacity-50"></i>
                        <p class="mb-0" data-i18n="emptyState">暂无项目日志，请在后台添加新的进展。</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($blogPosts as $post): ?>
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
                                <a href="<?php echo htmlspecialchars($link ?: '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank"
                                    rel="noopener noreferrer" class="read-more-btn">
                                    <span data-i18n="readMore">阅读完整文章</span> <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div class="mobile-bottom-nav">
        <button class="nav-btn nav-btn-ghost" id="mobileLangToggle"><i class="bi bi-translate"></i></button>
        <a class="nav-btn nav-btn-ghost" href="/rarelight/" data-i18n="navHome">返回首页</a>
        <a class="nav-btn nav-btn-primary flex-grow-1" href="/rarelight/dashboard" data-i18n="navLogin">进入课堂</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const FONT_KEY = 'rl_font_scale';
        const LANG_KEY = 'rl_lang';
        const htmlEl = document.documentElement;
        const fontSmallerBtn = document.getElementById('fontSmaller');
        const fontResetBtn = document.getElementById('fontReset');
        const fontLargerBtn = document.getElementById('fontLarger');
        const langToggle = document.getElementById('langToggle');
        const mobileLangToggle = document.getElementById('mobileLangToggle');
        let currentFontScale = 1;
        let currentLang = localStorage.getItem(LANG_KEY) || 'zh';

        const i18n = {
            zh: {
                brandTagline: 'Rare Light',
                brandTitle: 'Rare Light · 罕见病儿童公益',
                pageTitle: '项目日志 · Rare Light',
                navHome: '返回首页',
                navLogin: '进入课堂',
                heroBadge: '公益项目动态',
                heroTitle: '项目日志与进展',
                heroDesc: '记录 Rare Light 在罕见病儿童公益领域的行动点滴。这里的每一个脚印，都凝聚着爱与希望。',
                latestArticles: '最新文章',
                totalRecordsPrefix: '共',
                totalRecordsSuffix: '篇记录',
                emptyState: '暂无项目日志，请在后台添加新的进展。',
                readMore: '阅读完整文章'
            },
            en: {
                brandTagline: 'Rare Light',
                brandTitle: 'Rare Light · Rare Disease Care',
                pageTitle: 'Project Log · Rare Light',
                navHome: 'Home',
                navLogin: 'Enter Classroom',
                heroBadge: 'Project Updates',
                heroTitle: 'Project Log & Progress',
                heroDesc: 'Recording Rare Light\'s actions in rare disease child care. Every footprint here embodies love and hope.',
                latestArticles: 'Latest Articles',
                totalRecordsPrefix: 'Total',
                totalRecordsSuffix: 'records',
                emptyState: 'No project logs available. Please add new progress in the backend.',
                readMore: 'Read Full Article'
            }
        };

        function applyFontScale(scale) {
            currentFontScale = Math.min(1.3, Math.max(0.85, scale));
            htmlEl.style.setProperty('--font-base', '16px');
            htmlEl.style.fontSize = `calc(var(--font-base) * ${currentFontScale})`;
            localStorage.setItem(FONT_KEY, String(currentFontScale));
        }

        function initFontControls() {
            const saved = parseFloat(localStorage.getItem(FONT_KEY) || '1');
            if (!Number.isNaN(saved)) {
                applyFontScale(saved);
            }
            if (fontSmallerBtn) fontSmallerBtn.addEventListener('click', () => applyFontScale(currentFontScale - 0.05));
            if (fontResetBtn) fontResetBtn.addEventListener('click', () => applyFontScale(1));
            if (fontLargerBtn) fontLargerBtn.addEventListener('click', () => applyFontScale(currentFontScale + 0.05));
        }

        function applyTranslations(lang) {
            const dict = i18n[lang] || i18n.zh;
            document.querySelectorAll('[data-i18n]').forEach((el) => {
                const key = el.dataset.i18n;
                if (!key || !(key in dict)) return;
                el.textContent = dict[key];
            });
            document.title = dict.pageTitle || 'Project Log · Rare Light';
            currentLang = lang;
            localStorage.setItem(LANG_KEY, currentLang);

            const langText = lang === 'zh' ? '<i class="bi bi-translate me-1"></i> 中文 / EN' : '<i class="bi bi-translate me-1"></i> EN / 中文';
            if (langToggle) langToggle.innerHTML = langText;
        }

        function initLangToggle() {
            applyTranslations(currentLang);
            const toggleHandler = () => {
                const next = currentLang === 'zh' ? 'en' : 'zh';
                applyTranslations(next);
            };
            if (langToggle) langToggle.addEventListener('click', toggleHandler);
            if (mobileLangToggle) mobileLangToggle.addEventListener('click', toggleHandler);
        }

        initFontControls();
        initLangToggle();
    </script>
</body>

</html>