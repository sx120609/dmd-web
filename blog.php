<!DOCTYPE html>
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

        .post-card {
            border-radius: 18px;
            border: var(--card-border);
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            padding: 1.4rem 1.5rem;
        }

        .post-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 0.75rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .post-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0.6rem 0 0.6rem;
        }

        .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .post-tags span {
            padding: 0.15rem 0.6rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .post-body {
            color: #475569;
            line-height: 1.7;
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
        <p class="section-subtitle">将公众号文章链接填到卡片里，展示对外传播内容。</p>
        <div class="wechat-grid">
            <!-- 复制卡片并替换链接/标题/摘要 -->
            <article class="wechat-card">
                <div class="wechat-meta">
                    <span>2026-01-29</span>
                    <span>公众号推文</span>
                </div>
                <div class="wechat-title">示例：罕见病儿童线上课堂阶段成果</div>
                <p class="wechat-summary">这里填写文章摘要，简要说明内容亮点与结论。</p>
                <a class="wechat-link" href="https://mp.weixin.qq.com/" target="_blank" rel="noopener noreferrer">
                    阅读原文 →
                </a>
            </article>
            <article class="wechat-card">
                <div class="wechat-meta">
                    <span>2026-01-15</span>
                    <span>公众号推文</span>
                </div>
                <div class="wechat-title">示例：志愿者课堂故事与学生反馈</div>
                <p class="wechat-summary">这里填写文章摘要，简要说明内容亮点与结论。</p>
                <a class="wechat-link" href="https://mp.weixin.qq.com/" target="_blank" rel="noopener noreferrer">
                    阅读原文 →
                </a>
            </article>
            <article class="wechat-card">
                <div class="wechat-meta">
                    <span>2026-01-02</span>
                    <span>公众号推文</span>
                </div>
                <div class="wechat-title">示例：线下科普行走进医院</div>
                <p class="wechat-summary">这里填写文章摘要，简要说明内容亮点与结论。</p>
                <a class="wechat-link" href="https://mp.weixin.qq.com/" target="_blank" rel="noopener noreferrer">
                    阅读原文 →
                </a>
            </article>
        </div>
    </section>

    <div class="blog-layout">
        <section class="d-flex flex-column gap-4" id="postList">
            <div class="post-card text-secondary">正在加载内容...</div>
        </section>

        <aside class="sticky-side">
            <div class="side-card">
                <h3>如何新增内容</h3>
                <ol class="mb-0 text-secondary">
                    <li>进入管理后台 → 项目日志</li>
                    <li>新增文章并保存</li>
                    <li>刷新本页面即可展示</li>
                </ol>
                <div class="mt-3">
                    <a class="btn btn-outline-primary btn-sm" href="/rarelight/admin">进入后台编辑</a>
                </div>
            </div>
            <div class="side-card">
                <h3>展示建议</h3>
                <ul class="mb-0 text-secondary">
                    <li>每周 1 条进展</li>
                    <li>阶段性放截图或链接</li>
                    <li>突出“产出与进度”</li>
                </ul>
            </div>
        </aside>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const BASE_PATH = '/rarelight';
    const API_BASE = `${BASE_PATH}/api`;
    const postListEl = document.getElementById('postList');

    function escapeHtml(str = '') {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function summarize(text = '', maxLength = 140) {
        const clean = String(text).replace(/\s+/g, ' ').trim();
        if (clean.length <= maxLength) return clean;
        return `${clean.slice(0, maxLength)}…`;
    }

    function formatDate(value) {
        if (!value) return '';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return value;
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    }

    function renderPosts(posts) {
        postListEl.innerHTML = '';
        if (!Array.isArray(posts) || posts.length === 0) {
            postListEl.innerHTML = '<div class="post-card text-secondary">暂无日志内容，请先在后台新增文章。</div>';
            return;
        }
        posts.forEach((post) => {
            const tags = (post.tags || '').split(',').map((tag) => tag.trim()).filter(Boolean);
            const summary = post.summary && post.summary.trim() ? post.summary : summarize(post.content || '', 160);
            const metaParts = [
                formatDate(post.created_at),
                post.author ? `负责人：${escapeHtml(post.author)}` : ''
            ].filter(Boolean);
            const tagHtml = tags.length
                ? `<div class="post-tags">${tags.map((tag) => `<span>${escapeHtml(tag)}</span>`).join('')}</div>`
                : '';
            const card = document.createElement('article');
            card.className = 'post-card';
            card.innerHTML = `
                <div class="post-meta">${metaParts.map((item) => `<span>${item}</span>`).join('')}</div>
                <h2 class="post-title">${escapeHtml(post.title || '未命名文章')}</h2>
                ${tagHtml}
                <p class="post-body mb-0">${escapeHtml(summary)}</p>
            `;
            postListEl.appendChild(card);
        });
    }

    async function loadPosts() {
        try {
            const response = await fetch(`${API_BASE}/blog_posts.php`);
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data && (data.message || data.error) || '加载失败');
            }
            renderPosts(data.posts || []);
        } catch (error) {
            postListEl.innerHTML = `<div class="post-card text-danger">加载失败：${escapeHtml(error.message)}</div>`;
        }
    }

    loadPosts();
</script>
</body>
</html>
